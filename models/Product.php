<?php
/**
 * Product Model
 */
require_once 'models/BaseModel.php';

class Product extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureSchema();
    }

    private function ensureSchema()
    {
        $this->ensureColumnExists('products', 'weight_grams', "ALTER TABLE products ADD COLUMN weight_grams INT NOT NULL DEFAULT 0 AFTER sale_price");
        $this->ensureColumnExists('products', 'free_shipping', "ALTER TABLE products ADD COLUMN free_shipping TINYINT(1) NOT NULL DEFAULT 0 AFTER weight_grams");
    }

    private function ensureColumnExists($table, $column, $alterSql)
    {
        $stmt = $this->conn->prepare("SHOW COLUMNS FROM `{$table}` LIKE :column");
        $stmt->execute([':column' => $column]);

        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->conn->exec($alterSql);
        }
    }

    public function getAll($search = null)
    {
        // specific query to join categories and parent categories
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id";

        if ($search) {
            $sql .= " WHERE p.title LIKE :search OR p.sku LIKE :search";
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($sql);

        if ($search) {
            $term = "%$search%";
            $stmt->bindParam(':search', $term);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Insert Core Product
            $sql = "INSERT INTO products (
                title, slug, sku, price, sale_price, weight_grams, free_shipping, description, 
                main_image, is_featured, category_id, size_guide_id
            ) VALUES (
                :title, :slug, :sku, :price, :sale_price, :weight_grams, :free_shipping, :description, 
                :main_image, :is_featured, :category_id, :size_guide_id
            )";

            $stmt = $this->conn->prepare($sql);

            $slug = $this->createSlug($data['title']);
            // Avoid duplicate slug collision by appending timestamp if needed, 
            // but for now simple slug.

            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':sku', $data['sku']);
            $stmt->bindParam(':price', $data['price']);

            // Handle optional fields
            $salePrice = !empty($data['sale_price']) ? $data['sale_price'] : null;
            $stmt->bindParam(':sale_price', $salePrice);
            $weightGrams = max(0, (int) ($data['weight_grams'] ?? 0));
            $stmt->bindParam(':weight_grams', $weightGrams);
            $freeShipping = !empty($data['free_shipping']) ? 1 : 0;
            $stmt->bindParam(':free_shipping', $freeShipping);

            $stmt->bindParam(':description', $data['description']);
            $stmt->bindParam(':main_image', $data['main_image']);

            // Fix: isset check is always true for boolean false. Use !empty or direct cast.
            $isFeatured = !empty($data['is_featured']) ? 1 : 0;
            $stmt->bindParam(':is_featured', $isFeatured);

            $stmt->bindParam(':category_id', $data['category_id']);

            $sizeGuideId = !empty($data['size_guide_id']) ? $data['size_guide_id'] : null;
            $stmt->bindParam(':size_guide_id', $sizeGuideId);

            $stmt->execute();
            $productId = $this->conn->lastInsertId();

            // 2. Insert Gallery Images
            if (!empty($data['gallery_images'])) {
                $sqlImg = "INSERT INTO product_images (product_id, image_path) VALUES (:pid, :path)";
                $stmtImg = $this->conn->prepare($sqlImg);

                foreach ($data['gallery_images'] as $path) {
                    $stmtImg->bindParam(':pid', $productId);
                    $stmtImg->bindParam(':path', $path);
                    $stmtImg->execute();
                }
            }

            // 3. Insert Variation Links
            // Expecting data['variations'] to be array of [variation_id, variation_value_id]
            if (!empty($data['variations'])) {
                $sqlVar = "INSERT INTO product_variations (product_id, variation_id, variation_value_id) VALUES (:pid, :vid, :vvid)";
                $stmtVar = $this->conn->prepare($sqlVar);

                foreach ($data['variations'] as $var) {
                    $stmtVar->bindParam(':pid', $productId);
                    $stmtVar->bindParam(':vid', $var['variation_id']);
                    $stmtVar->bindParam(':vvid', $var['variation_value_id']);
                                        $stmtVar->execute();
                }
            }

            // 4. Insert Multi-Categories
            if (!empty($data['categories']) && is_array($data['categories'])) {
                $sqlCat = "INSERT INTO product_categories (product_id, category_id) VALUES (:pid, :cid)";
                $stmtCat = $this->conn->prepare($sqlCat);

                foreach ($data['categories'] as $catId) {
                    $stmtCat->bindParam(':pid', $productId);
                    $stmtCat->bindParam(':cid', $catId);
                    $stmtCat->execute();
                }
            }

            $this->conn->commit();

            return $productId;

        } catch (Exception $e) {
            $this->conn->rollBack();
            // Log error in production
            return false;
        }
    }

    public function update($data)
    {
        try {
            $this->conn->beginTransaction();

            // 1. Update Core Product
            $sql = "UPDATE products SET 
                    title = :title, 
                    slug = :slug, 
                    sku = :sku, 
                    price = :price, 
                    sale_price = :sale_price, 
                    weight_grams = :weight_grams,
                    free_shipping = :free_shipping,
                    description = :description, 
                    is_featured = :is_featured, 
                    category_id = :category_id, 
                    size_guide_id = :size_guide_id
                    WHERE id = :id";

            // Only update main_image if a new one is provided or we strictly want to
            if (!empty($data['main_image'])) {
                $sql = "UPDATE products SET 
                        title = :title, 
                        slug = :slug, 
                        sku = :sku, 
                        price = :price, 
                        sale_price = :sale_price, 
                        weight_grams = :weight_grams,
                        free_shipping = :free_shipping,
                        description = :description, 
                        main_image = :main_image,
                        is_featured = :is_featured, 
                        category_id = :category_id, 
                        size_guide_id = :size_guide_id
                        WHERE id = :id";
            }

            $stmt = $this->conn->prepare($sql);

            $slug = $this->createSlug($data['title']);

            $stmt->bindParam(':id', $data['id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':slug', $slug);
            $stmt->bindParam(':sku', $data['sku']);
            $stmt->bindParam(':price', $data['price']);

            $salePrice = !empty($data['sale_price']) ? $data['sale_price'] : null;
            $stmt->bindParam(':sale_price', $salePrice);
            $weightGrams = max(0, (int) ($data['weight_grams'] ?? 0));
            $stmt->bindParam(':weight_grams', $weightGrams);
            $freeShipping = !empty($data['free_shipping']) ? 1 : 0;
            $stmt->bindParam(':free_shipping', $freeShipping);

            $stmt->bindParam(':description', $data['description']);

            if (!empty($data['main_image'])) {
                $stmt->bindParam(':main_image', $data['main_image']);
            }

            $isFeatured = !empty($data['is_featured']) ? 1 : 0;
            $stmt->bindParam(':is_featured', $isFeatured);

            $stmt->bindParam(':category_id', $data['category_id']);

            $sizeGuideId = !empty($data['size_guide_id']) ? $data['size_guide_id'] : null;
            $stmt->bindParam(':size_guide_id', $sizeGuideId);

            $stmt->execute();

            // Check if any row was actually updated
            // Note: If values are identical to existing, MySQL might return 0 affected rows depending on flags.
            // But usually for an ID based update, if ID exists, it returns 1 or 0.
            // If ID matches nothing, it returns 0.
            // We want to ensure we don't return false if data was just identical (silent success), 
            // but we MUST fail if ID was wrong.
            // However, user specifically asked: "return true only when the update is successful and at least one row is affected."
            // So we will enforce rowCount > 0 condition for strictness.

            $mainUpdateSuccess = $stmt->rowCount() > 0;

            // 2. Append New Gallery Images
            if (!empty($data['new_gallery_images'])) {
                $sqlImg = "INSERT INTO product_images (product_id, image_path) VALUES (:pid, :path)";
                $stmtImg = $this->conn->prepare($sqlImg);

                foreach ($data['new_gallery_images'] as $path) {
                    $stmtImg->bindParam(':pid', $data['id']);
                    $stmtImg->bindParam(':path', $path);
                    $stmtImg->execute();
                    $mainUpdateSuccess = true; // Consider success if we added images
                }
            }

            // 3. Update Variations
            if (isset($data['variations'])) {
                // Delete existing
                $sqlDel = "DELETE FROM product_variations WHERE product_id = :pid";
                $stmtDel = $this->conn->prepare($sqlDel);
                $stmtDel->bindParam(':pid', $data['id']);
                $stmtDel->execute();

                // If we deleted vars, that's a change too
                if ($stmtDel->rowCount() > 0) {
                    $mainUpdateSuccess = true;
                }

                if (!empty($data['variations'])) {
                    $sqlVar = "INSERT INTO product_variations (product_id, variation_id, variation_value_id) VALUES (:pid, :vid, :vvid)";
                    $stmtVar = $this->conn->prepare($sqlVar);

                    foreach ($data['variations'] as $var) {
                        $stmtVar->bindParam(':pid', $data['id']);
                        $stmtVar->bindParam(':vid', $var['variation_id']);
                        $stmtVar->bindParam(':vvid', $var['variation_value_id']);
                        $stmtVar->execute();
                        $mainUpdateSuccess = true; // Consider success if we added vars
                    }
                }
                        }

            // 4. Update Multi-Categories
            if (isset($data['categories'])) { // Only if sent
                // Delete existing
                $sqlDel = "DELETE FROM product_categories WHERE product_id = :pid";
                $stmtDel = $this->conn->prepare($sqlDel);
                $stmtDel->bindParam(':pid', $data['id']);
                $stmtDel->execute();
                
                if ($stmtDel->rowCount() > 0) {
                     $mainUpdateSuccess = true;
                }

                // Insert new
                if (!empty($data['categories']) && is_array($data['categories'])) {
                    $sqlCat = "INSERT INTO product_categories (product_id, category_id) VALUES (:pid, :cid)";
                    $stmtCat = $this->conn->prepare($sqlCat);

                    foreach ($data['categories'] as $catId) {
                        $stmtCat->bindParam(':pid', $data['id']);
                        $stmtCat->bindParam(':cid', $catId);
                        $stmtCat->execute();
                        $mainUpdateSuccess = true;
                    }
                }
            }

            $this->conn->commit();

            // Return true if at least something changed (Main Product, Images, or Variations)
            return $mainUpdateSuccess;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }

    public function delete($id)
    {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function deleteAll()
    {
        $sql = "DELETE FROM products";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute();
    }

    private function createSlug($string)
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
    }

    /**
     * Get Featured Products
     */
    public function getFeatured($limit = 6)
    {
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id
                WHERE p.is_featured = 1 AND p.is_active = 1
                ORDER BY p.created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Latest Products
     */
    public function getLatest($limit = 6)
    {
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id
                WHERE p.is_active = 1
                ORDER BY p.created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get On Sale Products
     */
    public function getOnSale($limit = 6)
    {
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id
                WHERE p.sale_price IS NOT NULL AND p.sale_price < p.price AND p.is_active = 1
                ORDER BY p.created_at DESC LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get All On Sale Products (For Discounts Page)
     */
    public function getAllOnSale()
    {
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id
                WHERE p.sale_price IS NOT NULL AND p.sale_price < p.price AND p.is_active = 1
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Get All Featured Products (No Limit)
     */
    public function getAllFeatured()
    {
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id
                WHERE p.is_featured = 1 AND p.is_active = 1
                ORDER BY p.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFreeShippingProducts($limit = null)
    {
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN categories pc ON c.parent_id = pc.id
                WHERE p.free_shipping = 1 AND p.is_active = 1
                ORDER BY p.created_at DESC";

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
        }

        $stmt = $this->conn->prepare($sql);
        if ($limit !== null) {
            $stmt->bindValue(':limit', (int) $limit, PDO::PARAM_INT);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Get Single Product by ID
     */
    public function getById($id)
    {
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name, sg.image_path as size_guide_image
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id
                LEFT JOIN size_guides sg ON p.size_guide_id = sg.id
                WHERE p.id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get Gallery Images
     */
    public function getGalleryImages($productId)
    {
        $sql = "SELECT image_path FROM product_images WHERE product_id = :pid";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':pid', $productId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN); // Returns array of strings
    }

    /**
     * Get Product Categories (IDs)
     */
    public function getProductCategoryIds($productId)
    {
        $sql = "SELECT category_id FROM product_categories WHERE product_id = :pid";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':pid', $productId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }


    /**
     * Get Product Variations
     * Grouped by Variation Name (e.g. Color => [ {val_id, val_name}, ... ])
     */
    public function getVariations($productId)
    {
        // Join product_variations -> variations, variation_values
        $sql = "SELECT v.name as var_name, vv.id as val_id, vv.value as val_name, vv.color_hex
                FROM product_variations pv
                JOIN variations v ON pv.variation_id = v.id
                JOIN variation_values vv ON pv.variation_value_id = vv.id
                WHERE pv.product_id = :pid
                ORDER BY v.id, vv.id"; // Order ensures grouping works easily

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':pid', $productId);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Grouping logic
        $grouped = [];
        foreach ($rows as $row) {
            $name = $row['var_name']; // e.g. "Color" or "Size"
            if (!isset($grouped[$name])) {
                $grouped[$name] = [];
            }
            $grouped[$name][] = [
                'id' => $row['val_id'],
                'value' => $row['val_name'],
                'hex' => $row['color_hex']
            ];
        }
        return $grouped;
    }

    /**
     * Get Related Products (Same category, excluding current)
     */
    public function getRelated($categoryId, $excludeId, $limit = 4)
    {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.category_id = :catId AND p.id != :excludeId AND p.is_active = 1
                ORDER BY RAND() LIMIT :limit";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':catId', $categoryId);
        $stmt->bindParam(':excludeId', $excludeId);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Filtered Products (Price Range)
     * Safe, clean implementation
     */
    public function getFiltered($minPrice = null, $maxPrice = null, $search = null, $categoryIds = [])
    {
        $sql = "SELECT p.*, c.name as category_name, pc.name as parent_category_name
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id
                WHERE 1=1";

        // Params array for execution
        $params = [];

        // 1. Price Filter (Standard)
        if (!empty($minPrice)) {
            $sql .= " AND p.price >= :minPrice";
            $params[':minPrice'] = $minPrice;
        }

        if (!empty($maxPrice)) {
            $sql .= " AND p.price <= :maxPrice";
            $params[':maxPrice'] = $maxPrice;
        }

        // 2. Search Filter
        if (!empty($search)) {
            $sql .= " AND (p.title LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = "%$search%";
        }

        // 3. Category Filter (Array of IDs)
        // If categories are selected, we filter products that match ANY of these IDs.
        if (!empty($categoryIds) && is_array($categoryIds)) {
            // Create placeholders: ?, ?, ?
            // Since we use named params elsewhere, we can mix if careful or use named params with index
            // Safest with PDO is IN clause with generated named keys.

            $inQuery = "";
            foreach ($categoryIds as $i => $id) {
                $key = ":cat" . $i;
                $inQuery .= ($inQuery ? ", " : "") . $key;
                $params[$key] = $id;
            }

            if (!empty($inQuery)) {
                // Multi-Category Support
                // Check Primary Cat OR Primary Parent OR Multi-Cat OR Multi-Cat Parent
                $sql .= " AND (
                            p.category_id IN ($inQuery) 
                            OR c.parent_id IN ($inQuery)
                            OR EXISTS (
                                SELECT 1 FROM product_categories pc_multi 
                                LEFT JOIN categories c_multi ON pc_multi.category_id = c_multi.id
                                WHERE pc_multi.product_id = p.id 
                                AND (pc_multi.category_id IN ($inQuery) OR c_multi.parent_id IN ($inQuery))
                            )
                        )";
            }
        }

        $sql .= " AND p.is_active = 1";
        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function toggleActive($id)
    {
        $sql = "UPDATE products SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>
