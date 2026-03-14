<?php
/**
 * Shop Controller
 * Handles Public Product Browsing
 */
require_once 'models/Product.php';
require_once 'models/Category.php';
require_once 'models/Variation.php';
require_once 'models/Setting.php';
require_once 'models/DeliverySetting.php';
require_once 'helpers/DeliveryHelper.php';
require_once 'helpers/SeoHelper.php';

class ShopController extends BaseController
{
    private $productModel;
    private $categoryModel;
    private $settingModel;
    private $deliverySettingModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->settingModel = new Setting();
        $this->deliverySettingModel = new DeliverySetting();
    }

    // List all products (Shop Index / Search Results)
    public function index()
    {
        // 1. Get Filters
        $search = $_GET['search'] ?? null;
        $min = $_GET['min'] ?? null;
        $max = $_GET['max'] ?? null;
        $catParam = $_GET['cat'] ?? null;

        $categoryIds = [];
        if (!empty($catParam)) {
            $categoryIds = explode(',', $catParam);
            $categoryIds = array_filter($categoryIds, 'is_numeric');
        }

        // 2. Fetch Data
        // Use getFiltered to handle all filter cases (Search + Price + Category)
        $products = $this->productModel->getFiltered($min, $max, $search, $categoryIds);
        $categories = $this->categoryModel->getAll();
        $settings = $this->settingModel->getAllPairs();

        // 3. Prepare View Data
        $title = 'Shop';
        if ($search) {
            $title = 'Search Results for "' . htmlspecialchars($search) . '"';
        }

        // Fetch Specific Category Details if Filtered
        $currentCategory = null;
        if (!empty($categoryIds) && count($categoryIds) === 1) {
            // Only strictly needed when ONE category is selected (Sub-Category View)
            $currentCategoryId = reset($categoryIds);
            $currentCategory = $this->categoryModel->getById($currentCategoryId);

            // If it has a parent, fetch parent too for breadcrumb
            if ($currentCategory && $currentCategory['parent_id']) {
                $parentCat = $this->categoryModel->getById($currentCategory['parent_id']);
                $currentCategory['parent_name'] = $parentCat['name'];
                $currentCategory['parent_id'] = $parentCat['id'];
            }
        }

        // 4. Load View
        $seoTitle = 'Shop | ' . SeoHelper::shopName($settings);
        $seoDescription = SeoHelper::trimText('Browse products from ' . SeoHelper::shopName($settings) . '.', 160);
        $seoCanonical = SeoHelper::absoluteUrl(BASE_URL . 'shop/index');
        $seoRobots = 'index,follow';
        $seoImage = SeoHelper::normalizeAssetUrl($settings['shop_logo'] ?? '');
        $breadcrumbs = [
            ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
            ['name' => 'Shop', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'shop/index')]
        ];

        if ($search) {
            $seoTitle = 'Search Results for "' . $search . '" | ' . SeoHelper::shopName($settings);
            $seoDescription = SeoHelper::trimText('Search results for "' . $search . '" in ' . SeoHelper::shopName($settings) . '.', 160);
            $seoRobots = 'noindex,follow';
        } elseif ($currentCategory) {
            $seoTitle = ($currentCategory['name'] ?? 'Category') . ' | ' . SeoHelper::shopName($settings);
            $seoDescription = SeoHelper::trimText('Browse ' . ($currentCategory['name'] ?? 'category') . ' products from ' . SeoHelper::shopName($settings) . '.', 160);
            $seoRobots = 'noindex,follow';
            $categoryImage = SeoHelper::productImageUrl($currentCategory['image'] ?? '');
            if (!empty($categoryImage)) {
                $seoImage = $categoryImage;
            }
        } elseif (!empty($catParam) || !empty($min) || !empty($max)) {
            $seoRobots = 'noindex,follow';
        }

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'seo_canonical' => $seoCanonical,
            'seo_image' => $seoImage,
            'seo_robots' => $seoRobots,
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema($breadcrumbs)
            ]
        ]);

        $this->view('customer/shop/index', [
            'title' => $title,
            'products' => $products,
            'categories' => $categories,
            'settings' => $settings,
            'search_query' => $search,
            'currentCategory' => $currentCategory, // Pass to view
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    // Single Product View
    public function product($id)
    {
        $product = $this->productModel->getById($id);

        if (!$product) {
         // Handle 404
         http_response_code(404);
         require_once 'views/errors/404.php';
         return;
     }

        // Fetch additional details
        $gallery = $this->productModel->getGalleryImages($id);
        $variations = $this->productModel->getVariations($id);
        $relatedProducts = $this->productModel->getRelated($product['category_id'], $id, 3);

        // Fetch Categories for Sidebar
        $categories = $this->categoryModel->getAll();

        // Pass global settings for currency etc
        $settings = $this->settingModel->getAllPairs();
        $productImage = SeoHelper::productImageUrl($product['main_image'] ?? '');
        $productUrl = SeoHelper::absoluteUrl(BASE_URL . 'shop/product/' . $product['id']);
        $breadcrumbs = [
            ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
            ['name' => 'Shop', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'shop/index')]
        ];
        if (!empty($product['category_name'])) {
            $breadcrumbs[] = ['name' => $product['category_name'], 'url' => SeoHelper::absoluteUrl(BASE_URL . 'shop/category/' . $product['category_id'])];
        }
        $breadcrumbs[] = ['name' => $product['title'], 'url' => $productUrl];

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => $product['title'] . ' | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText($product['description'] ?? ($product['title'] ?? ''), 160),
            'seo_canonical' => $productUrl,
            'seo_image' => $productImage,
            'seo_type' => 'product',
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema($breadcrumbs),
                SeoHelper::buildProductSchema($settings, $product, $productImage, $productUrl)
            ]
        ]);

        $this->view('customer/shop/product', [
            'title' => $product['title'],
            'product' => $product,
            'gallery' => $gallery,
            'variations' => $variations,
            'relatedProducts' => $relatedProducts,
            'categories' => $categories, // For sidebar
            'settings' => $settings,
            'deliveryDistricts' => DeliveryHelper::districtList(),
            'deliveryRatesMap' => $this->deliverySettingModel->getRatesMap(),
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    // AJAX Filter Handler (Price Range)
    public function filter()
    {
        $min = $_GET['min'] ?? null;
        $max = $_GET['max'] ?? null;
        $search = $_GET['search'] ?? null;

        // Handle Category Filter (Comma separated IDs: 1,2,3)
        $catParam = $_GET['cat'] ?? null;
        $categoryIds = [];
        if (!empty($catParam)) {
            $categoryIds = explode(',', $catParam);
            // Sanitize integers
            $categoryIds = array_filter($categoryIds, 'is_numeric');
        }

        // Fetch Settings for Currency Symbol
        $settings = $this->settingModel->getAllPairs();

        // Get Filtered Products
        $products = $this->productModel->getFiltered($min, $max, $search, $categoryIds);

        if (empty($products)) {
            echo '<div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #777;">
                    <h3>No products found.</h3>
                    <p>Try adjusting your price range.</p>
                  </div>';
            return;
        }

        // Render Partial HTML
        foreach ($products as $prod) {
            include 'views/customer/partials/product_card.php';
        }
    }

    // --- New Desktop Pages (Task 6.3 Reuse Strategy) ---

    // 1. Sales Page (UI: "Discounts!")
    public function sales()
    {
        // Fetch On Sale Items using existing Model logic
        $products = $this->productModel->getAllOnSale();
        $categories = $this->categoryModel->getAll();
        $settings = $this->settingModel->getAllPairs();
        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Discounts! | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText('Browse sale products and discount deals from ' . SeoHelper::shopName($settings) . '.', 160),
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'shop/sales'),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
                    ['name' => 'Discounts!', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'shop/sales')]
                ])
            ]
        ]);

        $this->view('customer/shop/index', [
            'title' => 'Discounts!',
            'products' => $products,
            'categories' => $categories,
            'settings' => $settings,
            'isSpecialPage' => true, // Flag to trigger custom header in view
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    // 2. Featured Page (UI: "Featured Products")
    public function featured()
    {
        // Fetch Featured
        $products = $this->productModel->getFeatured(20); // Limit 20 for now
        $categories = $this->categoryModel->getAll();
        $settings = $this->settingModel->getAllPairs();
        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Featured Products | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText('Explore featured products from ' . SeoHelper::shopName($settings) . '.', 160),
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'shop/featured'),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
                    ['name' => 'Featured Products', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'shop/featured')]
                ])
            ]
        ]);

        $this->view('customer/shop/index', [
            'title' => 'Featured Products',
            'products' => $products,
            'categories' => $categories,
            'settings' => $settings,
            'isSpecialPage' => true,
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    // 3. New Arrivals (UI: "Recent Items")
    public function new_arrivals()
    {
        // Fetch Latest
        $products = $this->productModel->getLatest(20);
        $categories = $this->categoryModel->getAll();
        $settings = $this->settingModel->getAllPairs();
        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Recent Items | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText('See the latest arrivals from ' . SeoHelper::shopName($settings) . '.', 160),
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'shop/new_arrivals'),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
                    ['name' => 'Recent Items', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'shop/new_arrivals')]
                ])
            ]
        ]);

        $this->view('customer/shop/index', [
            'title' => 'Recent Items',
            'products' => $products,
            'categories' => $categories,
            'settings' => $settings,
            'isSpecialPage' => true,
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    // List All Categories Page
    public function categories()
    {
        $categories = $this->categoryModel->getAll();
        $settings = $this->settingModel->getAllPairs();
        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'All Categories | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText('Browse all product categories from ' . SeoHelper::shopName($settings) . '.', 160),
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'shop/categories'),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
                    ['name' => 'All Categories', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'shop/categories')]
                ])
            ]
        ]);

        $this->view('customer/shop/categories', [
            'title' => 'All Categories',
            'categories' => $categories,
            'settings' => $settings,
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }
    // Single Category Detail Page
    public function category($id)
    {
        // 1. Get Main Category
        $category = $this->categoryModel->getById($id);

        if (!$category) {
            // Fallback or 404
            $this->redirect('shop/categories');
            return;
        }

        // 2. Get Sub-Categories (Filter from all)
        $allCats = $this->categoryModel->getAll();
        $subCategories = array_filter($allCats, function ($c) use ($id) {
            return $c['parent_id'] == $id;
        });

        // 3. Get Products (Include Main + Sub Categories)
        $catIds = array_column($subCategories, 'id');
        $catIds[] = $id;

        // Pass IDs to filter
        $products = $this->productModel->getFiltered(null, null, null, $catIds);

        $settings = $this->settingModel->getAllPairs();
        $categoryImage = SeoHelper::productImageUrl($category['image'] ?? '');
        $categoryUrl = SeoHelper::absoluteUrl(BASE_URL . 'shop/category/' . $category['id']);
        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => $category['name'] . ' | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText('Browse ' . $category['name'] . ' products from ' . SeoHelper::shopName($settings) . '.', 160),
            'seo_canonical' => $categoryUrl,
            'seo_image' => $categoryImage ?: SeoHelper::normalizeAssetUrl($settings['shop_logo'] ?? ''),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
                    ['name' => 'Categories', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'shop/categories')],
                    ['name' => $category['name'], 'url' => $categoryUrl]
                ])
            ]
        ]);

        $this->view('customer/shop/category_detail', [
            'category' => $category,
            'subCategories' => $subCategories,
            'products' => $products,
            'settings' => $settings,
            'title' => $category['name'],
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }
    // --- Desktop Home Tabs AJAX Handler ---
    public function tab_content()
    {
        $type = $_GET['type'] ?? 'new';
        $products = [];

        if ($type === 'new') {
            $products = $this->productModel->getLatest(12); // Grid of 12
        } elseif ($type === 'featured') {
            $products = $this->productModel->getFeatured(12);
        } elseif ($type === 'sale') {
            $products = $this->productModel->getOnSale(12); // Use getOnSale (limit 12) not All
        }

        if (empty($products)) {
            echo '<p style="text-align:center; padding:20px; color:#777;">No products found.</p>';
            return;
        }

        foreach ($products as $prod) {
            include 'views/customer/partials/product_card.php';
        }
    }
}
?>
