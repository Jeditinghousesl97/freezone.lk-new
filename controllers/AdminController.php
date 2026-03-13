<?php
/**
 * Admin Controller
 * 
 * Handles the Developer/Shop Owner Dashboard.
 */
class AdminController extends BaseController
{

    /**
     * Dashboard Page
     */
    public function dashboard()
    {
        // Security Check: Must be logged in
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
            return;
        }

        // Connect to DB to get stats
        $db = (new Database())->getConnection();

        // 1. Get Counts
        $stats = [
            'products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
            'categories' => $db->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
            'feedbacks' => $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
            'size_guides' => $db->query("SELECT COUNT(*) FROM size_guides")->fetchColumn()
        ];

        // 2. Get Recent Products (Limit 5)
        // LEFT JOIN to get category name
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                ORDER BY p.created_at DESC LIMIT 10";
        $products = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // 3. Get Shop Settings (Logo/Name)
        require_once 'models/Setting.php';
        $settingModel = new Setting();
        $settings = $settingModel->getMultiple(['shop_name', 'shop_logo']);

        // Load the view
        $this->view('admin/dashboard', [
            'title' => 'Dashboard - EcomCMS',
            'stats' => $stats,
            'latest_products' => $products,
            'settings' => $settings
        ]);
    }

    /**
     * Settings Page
     * Logic: Shop Owner -> Gatekeeper View. Developer -> Full Settings View.
     */
    public function settings()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
            return;
        }

        // Check Role
        // Currently we only have 'developer' or 'owner' (default)
        // If it's a shop owner, show the gatekeeper

        if (isset($_SESSION['role']) && $_SESSION['role'] === 'developer') {
            // TODO: Create the Developer Settings View later
            echo "<h1>Developer Settings (Coming Soon)</h1>";
        } else {
            // Shop Owner View (Restricted)
            $this->view('admin/settings_gatekeeper', ['title' => 'Settings - Authenticate']);
        }
    }
}
?>