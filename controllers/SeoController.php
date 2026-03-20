<?php
require_once 'models/Setting.php';
require_once 'models/Product.php';
require_once 'models/Category.php';
require_once 'helpers/SeoHelper.php';

class SeoController extends BaseController
{
    private $settingModel;
    private $productModel;
    private $categoryModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
        $this->productModel = new Product();
        $this->categoryModel = new Category();
    }

    public function sitemap()
    {
        header('Content-Type: application/xml; charset=utf-8');

        $staticUrls = [
            SeoHelper::absoluteUrl(BASE_URL),
            SeoHelper::absoluteUrl(BASE_URL . 'shop/index'),
            SeoHelper::absoluteUrl(BASE_URL . 'shop/categories'),
            SeoHelper::absoluteUrl(BASE_URL . 'shop/featured'),
            SeoHelper::absoluteUrl(BASE_URL . 'shop/free_shipping'),
            SeoHelper::absoluteUrl(BASE_URL . 'shop/new_arrivals'),
            SeoHelper::absoluteUrl(BASE_URL . 'shop/sales'),
            SeoHelper::absoluteUrl(BASE_URL . 'discounts'),
            SeoHelper::absoluteUrl(BASE_URL . 'reviews'),
            SeoHelper::absoluteUrl(BASE_URL . 'contact'),
            SeoHelper::absoluteUrl(BASE_URL . 'page/refundReturns'),
            SeoHelper::absoluteUrl(BASE_URL . 'page/termsConditions'),
            SeoHelper::absoluteUrl(BASE_URL . 'page/privacyPolicy'),
        ];

        $products = $this->productModel->getAll();
        $categories = $this->categoryModel->getAll();

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($staticUrls as $url) {
            echo '<url><loc>' . htmlspecialchars($url, ENT_XML1) . '</loc></url>';
        }

        foreach ($categories as $category) {
            $url = SeoHelper::absoluteUrl(BASE_URL . 'shop/category/' . $category['id']);
            echo '<url><loc>' . htmlspecialchars($url, ENT_XML1) . '</loc></url>';
        }

        foreach ($products as $product) {
            if (empty($product['is_active'])) {
                continue;
            }
            $url = SeoHelper::absoluteUrl(BASE_URL . 'shop/product/' . $product['id']);
            echo '<url><loc>' . htmlspecialchars($url, ENT_XML1) . '</loc></url>';
        }

        echo '</urlset>';
        exit;
    }

    public function robots()
    {
        header('Content-Type: text/plain; charset=utf-8');
        echo "User-agent: *\n";
        echo "Allow: /\n";
        echo "Disallow: " . BASE_URL . "cart\n";
        echo "Disallow: " . BASE_URL . "auth/\n";
        echo "Disallow: " . BASE_URL . "admin/\n";
        echo "Disallow: " . BASE_URL . "settings/\n";
        echo "Disallow: " . BASE_URL . "order/\n";
        echo "Sitemap: " . SeoHelper::absoluteUrl(BASE_URL . 'sitemap.xml') . "\n";
        exit;
    }
}
