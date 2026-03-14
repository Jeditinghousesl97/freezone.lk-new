<?php
require_once 'models/Product.php';
require_once 'models/Setting.php';

class StockController extends BaseController
{
    private $productModel;
    private $settingModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->settingModel = new Setting();
    }

    private function requireAdminSession()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('auth/login');
        }
    }

    public function index()
    {
        $this->requireAdminSession();
        $overview = $this->productModel->getStockOverview();
        $settings = $this->settingModel->getAllPairs();

        $filter = trim((string) ($_GET['filter'] ?? ''));
        $products = array_values(array_filter($overview['products'], function ($product) use ($filter) {
            $snapshot = $product['stock_snapshot'] ?? [];
            if ($filter === 'out_of_stock') {
                return ($snapshot['status'] ?? '') === 'out_of_stock';
            }
            if ($filter === 'low_stock') {
                return ($snapshot['status'] ?? '') === 'low_stock';
            }
            if ($filter === 'variant') {
                return !empty($snapshot['has_variant_stock']);
            }
            return true;
        }));

        $this->view('admin/stock/index', [
            'title' => 'Stock Management',
            'settings' => $settings,
            'summary' => $overview['summary'],
            'products' => $products,
            'active_filter' => $filter
        ]);
    }
}
