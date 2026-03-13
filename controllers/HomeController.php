<?php
/**
 * Home Controller
 * Handles the landing page logic.
 */
require_once 'models/Product.php';
require_once 'models/Category.php';
require_once 'models/Settings.php';
require_once 'helpers/SeoHelper.php';

class HomeController extends BaseController
{
    private $productModel;
    private $categoryModel;
    private $settingsModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->categoryModel = new Category();
        $this->settingsModel = new Settings();
    }

    public function index()
    {
        // 1. Fetch Shop Settings
        $settings = $this->settingsModel->getAll();

        // 2. Fetch Categories (Top level)
        $categories = $this->categoryModel->getAll();

        // 3. Fetch Products
        $featuredProducts = $this->productModel->getAllFeatured();
        $latestProducts = $this->productModel->getLatest(8);
        $saleProducts = $this->productModel->getAllOnSale();

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => ($settings['shop_name'] ?? 'Shop') . ' | Online Store',
            'seo_description' => SeoHelper::trimText(($settings['shop_about'] ?? '') . ' Shop featured products, latest arrivals, categories, and offers.', 160),
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => $settings['shop_name'] ?? 'Home', 'url' => SeoHelper::absoluteUrl(BASE_URL)]
                ])
            ]
        ]);

        $this->view('customer/home', [
            'title' => $settings['shop_name'] ?? 'Home',
            'settings' => $settings,
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
            'latestProducts' => $latestProducts,
            'saleProducts' => $saleProducts,
            'seo_title' => $seo['seo_title'],
            'seo_description' => $seo['seo_description'],
            'seo_canonical' => $seo['seo_canonical'],
            'seo_image' => $seo['seo_image'],
            'seo_type' => $seo['seo_type'],
            'seo_robots' => $seo['seo_robots'],
            'seo_json_ld' => $seo['seo_json_ld']
        ]);
    }
}
?>
