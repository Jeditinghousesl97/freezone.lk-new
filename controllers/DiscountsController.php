<?php
/**
 * Discounts Controller
 * Handles the Discounts / Sale Page
 */
require_once 'models/Product.php';
require_once 'models/Setting.php';
require_once 'helpers/SeoHelper.php';

class DiscountsController extends BaseController
{
    private $productModel;
    private $settingModel;

    public function __construct()
    {
        $this->productModel = new Product();
        $this->settingModel = new Setting();
    }

    public function index()
    {
        // 1. Fetch All Discounted Products
        $products = $this->productModel->getAllOnSale();

        // 2. Fetch Related Products (Random/Featured for bottom section)
        // Using 'Featured' as a proxy for 'Related' or general engagement
        $relatedProducts = $this->productModel->getFeatured(6);

        // 3. Fetch Settings (for Colors, Currency, etc.)
        $settings = $this->settingModel->getAllPairs();
        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Discounts & Offers | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText('Browse current discounts, sale products, and limited-time offers from ' . SeoHelper::shopName($settings) . '.', 160),
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'discounts'),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
                    ['name' => 'Discounts & Offers', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'discounts')]
                ])
            ]
        ]);

        // 4. Load View
        $this->view('customer/discounts', [
            'title' => 'Discounts & Offers',
            'products' => $products,
            'relatedProducts' => $relatedProducts,
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
}
?>
