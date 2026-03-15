<?php
require_once 'models/Setting.php';
require_once 'helpers/SeoHelper.php';

class ContactController extends BaseController
{
    private $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    public function index()
    {
        $settings = $this->settingModel->getAllPairs();

        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Contact Us | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText('Contact ' . SeoHelper::shopName($settings) . ' using WhatsApp, email, and the shop website details shared on this page.', 160),
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'contact'),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
                    ['name' => 'Contact Us', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'contact')]
                ])
            ]
        ]);

        $this->view('customer/contact', [
            'title' => 'Contact Us',
            'settings' => $settings,
            'current_page' => 'contact',
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
