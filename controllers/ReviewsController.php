<?php
/**
 * Reviews Controller
 * Handles the Customer Feedback Page
 */
require_once 'models/Feedback.php';
require_once 'models/Setting.php';
require_once 'helpers/SeoHelper.php';

class ReviewsController extends BaseController
{
    private $feedbackModel;
    private $settingModel;

    public function __construct()
    {
        $this->feedbackModel = new Feedback();
        $this->settingModel = new Setting();
    }

    public function index()
    {
        // 1. Fetch Feedbacks (Images)
        $feedbacks = $this->feedbackModel->getAll();

        // 2. Fetch Settings (Shop Name, Logo, etc)
        $settings = $this->settingModel->getAllPairs();
        $seo = SeoHelper::defaultSeo($settings, [
            'seo_title' => 'Customer Reviews | ' . SeoHelper::shopName($settings),
            'seo_description' => SeoHelper::trimText('See customer reviews, shop feedback, and recent buyer experiences from ' . SeoHelper::shopName($settings) . '.', 160),
            'seo_canonical' => SeoHelper::absoluteUrl(BASE_URL . 'reviews'),
            'seo_json_ld' => [
                SeoHelper::buildOrganizationSchema($settings),
                SeoHelper::buildWebsiteSchema($settings),
                SeoHelper::buildBreadcrumbSchema([
                    ['name' => SeoHelper::shopName($settings), 'url' => SeoHelper::absoluteUrl(BASE_URL)],
                    ['name' => 'Customer Reviews', 'url' => SeoHelper::absoluteUrl(BASE_URL . 'reviews')]
                ])
            ]
        ]);

        // 3. Load View
        $this->view('customer/reviews', [
            'title' => 'Customer Reviews',
            'feedbacks' => $feedbacks,
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
