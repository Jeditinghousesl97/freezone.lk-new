<?php
/**
 * Admin Controller
 * 
 * Handles the Developer/Shop Owner Dashboard.
 */
class AdminController extends BaseController
{
    private function requireAdminSession()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
            return false;
        }

        return true;
    }

    /**
     * Dashboard Page
     */
    public function dashboard()
    {
        // Security Check: Must be logged in
        if (!$this->requireAdminSession()) {
            return;
        }

        // Connect to DB to get stats
        $db = (new Database())->getConnection();
        require_once 'models/Order.php';
        require_once 'models/Product.php';
        $orderModel = new Order();
        $productModel = new Product();
        $stockOverview = $productModel->getStockOverview();

        // 1. Get Counts
        $stats = [
            'products' => $db->query("SELECT COUNT(*) FROM products")->fetchColumn(),
            'categories' => $db->query("SELECT COUNT(*) FROM categories")->fetchColumn(),
            'feedbacks' => $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn(),
            'size_guides' => $db->query("SELECT COUNT(*) FROM size_guides")->fetchColumn(),
            'orders' => $orderModel->countAll(),
            'low_stock' => (int) ($stockOverview['summary']['low_stock'] ?? 0),
            'tracked_products' => (int) ($stockOverview['summary']['tracked_products'] ?? 0)
        ];
        $finance = $orderModel->getFinanceSummary([]);
        $chartRows = array_reverse($orderModel->getReportRows([], 14));

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
        $settings = $settingModel->getMultiple(['shop_name', 'shop_logo', 'currency_symbol']);

        // Load the view
        $this->view('admin/dashboard', [
            'title' => 'Dashboard - EcomCMS',
            'stats' => $stats,
            'finance' => $finance,
            'chart_rows' => $chartRows,
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
        if (!$this->requireAdminSession()) {
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

    public function serverCheck()
    {
        if (!$this->requireAdminSession()) {
            return;
        }

        require_once 'models/Setting.php';
        $settingModel = new Setting();
        $settings = $settingModel->getMultiple(['shop_name', 'shop_logo', 'currency_symbol']);

        $checks = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'gd_enabled' => extension_loaded('gd'),
            'imagick_enabled' => extension_loaded('imagick'),
            'webp_support' => function_exists('imagewebp'),
            'avif_support' => function_exists('imageavif'),
            'fileinfo_enabled' => extension_loaded('fileinfo'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time')
        ];

        $recommendations = [];
        if (!$checks['gd_enabled'] && !$checks['imagick_enabled']) {
            $recommendations[] = 'Enable GD or Imagick in your hosting control panel before we build automatic image resize and WebP generation.';
        }
        if ($checks['gd_enabled'] && !$checks['webp_support']) {
            $recommendations[] = 'GD is enabled, but WebP support is missing. Ask hosting support whether WebP was compiled into GD.';
        }
        if (empty($recommendations)) {
            $recommendations[] = 'Your server has the minimum image-processing capability needed for same-server optimized image delivery.';
        }

        $this->view('admin/server_check', [
            'title' => 'Server Check',
            'settings' => $settings,
            'checks' => $checks,
            'recommendations' => $recommendations
        ]);
    }

    public function imageOptimizer()
    {
        if (!$this->requireAdminSession()) {
            return;
        }

        require_once 'models/Setting.php';
        require_once 'helpers/ImageHelper.php';

        $settingModel = new Setting();
        $settings = $settingModel->getMultiple(['shop_name', 'shop_logo', 'currency_symbol']);

        $runSummary = null;
        $mode = 'scan';
        $batchLimit = 25;
        $totalOptimizable = 0;

        $totalOptimizable = count(ImageHelper::getOptimizableUploads());

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $mode = ($_POST['run_mode'] ?? 'missing') === 'rebuild' ? 'rebuild' : 'missing';
            $force = $mode === 'rebuild';
            $batchLimit = max(1, (int) ($_POST['limit'] ?? 25));
            $offset = max(0, (int) ($_POST['offset'] ?? 0));
            $runSummary = ImageHelper::optimizeExistingUploadsBatch($force, $batchLimit, $offset);
        }

        $uploadDir = ROOT_PATH . 'assets/uploads/';
        $derivedDir = ROOT_PATH . 'assets/uploads/derived/';
        $uploadCount = 0;
        $derivedCount = 0;

        if (is_dir($uploadDir)) {
            foreach (scandir($uploadDir) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                if (is_file($uploadDir . $entry)) {
                    $uploadCount++;
                }
            }
        }

        if (is_dir($derivedDir)) {
            foreach (scandir($derivedDir) ?: [] as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                if (is_file($derivedDir . $entry)) {
                    $derivedCount++;
                }
            }
        }

        $this->view('admin/image_optimizer', [
            'title' => 'Image Optimizer',
            'settings' => $settings,
            'upload_count' => $uploadCount,
            'derived_count' => $derivedCount,
            'run_summary' => $runSummary,
            'mode' => $mode,
            'batch_limit' => $batchLimit,
            'total_optimizable' => $totalOptimizable
        ]);
    }
}
?>
