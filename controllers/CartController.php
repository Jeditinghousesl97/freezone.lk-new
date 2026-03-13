<?php
class CartController extends BaseController
{
    public function index()
    {
        // 1. Fetch Settings
        require_once 'models/Setting.php';
        require_once 'helpers/SeoHelper.php';
        $settingModel = new Setting();
        $settings = $settingModel->getAllPairs();
        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'My Cart | ' . SeoHelper::shopName($settings),
            'seo_description' => 'Review the selected items in your shopping cart before checkout.',
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'cart'),
            'seo_robots' => 'noindex,nofollow',
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings)
            ]
        ]);

        // 2. Get Cart from Session
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

        // 3. Load View
        $this->view('customer/shop/cart', [
            'title' => 'My Cart',
            'settings' => $settings,
            'cart' => $cart,
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }

    // Add Item to Cart (AJAX)
    public function add()
    {
        // Accept JSON Input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input || empty($input['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        // Check for existing item (Same ID + Same Variations)
        $found = false;
        $qtyToAdd = isset($input['quantity']) ? (int) $input['quantity'] : 1;
        if ($qtyToAdd < 1)
            $qtyToAdd = 1;

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['id'] == $input['id'] && $item['variants'] == $input['variants']) {
                $item['qty'] += $qtyToAdd;
                $found = true;
                break;
            }
        }

        // Add new if not found
        if (!$found) {
            $_SESSION['cart'][] = [
                'id' => $input['id'],
                'title' => $input['title'],
                'price' => $input['price'],
                'img' => $input['img'], // URL passed from frontend 
                'variants' => $input['variants'],
                'qty' => $qtyToAdd
            ];
        }

        // Return Success
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'count' => array_sum(array_column($_SESSION['cart'], 'qty'))
        ]);
        exit;
    }

    // Remove Item (AJAX)
    public function remove()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $index = $input['index'] ?? null;

        if ($index !== null && isset($_SESSION['cart'][$index])) {
            array_splice($_SESSION['cart'], $index, 1);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'cart' => array_values($_SESSION['cart']), // Return new array
            'count' => isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'qty')) : 0
        ]);
        exit;
    }

    // Update Item Quantity (AJAX)
    public function updateQty()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $index = $input['index'] ?? null;
        $qty = isset($input['qty']) ? (int) $input['qty'] : 1;

        if ($index === null || !isset($_SESSION['cart'][$index])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
            exit;
        }

        $_SESSION['cart'][$index]['qty'] = max(1, $qty);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'cart' => array_values($_SESSION['cart']),
            'count' => array_sum(array_column($_SESSION['cart'], 'qty'))
        ]);
        exit;
    }

    // Clear Cart (AJAX)
    public function clear()
    {
        $_SESSION['cart'] = [];

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'count' => 0]);
        exit;
    }
}
?>
