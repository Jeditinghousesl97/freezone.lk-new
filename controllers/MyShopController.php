<?php
/**
 * My Shop Controller
 */
require_once 'models/Setting.php';

class MyShopController extends BaseController
{

    private $settingModel;

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    public function index()
    {
        // Fetch all relevant settings
        $keys = [
            'shop_qr',
            'shop_logo',
            'shop_url',
            'review_link',
            'social_fb',
            'social_tiktok',
            'social_insta',
            'social_youtube',
            'social_whatsapp',
            'hero_slide_1_image',
            'hero_slide_1_link',
            'hero_slide_2_image',
            'hero_slide_2_link',
            'hero_slide_3_image',
            'hero_slide_3_link',
            'refund_policy_content',
            'terms_conditions_content',
            'privacy_policy_content'
        ];

        $settings = $this->settingModel->getMultiple($keys);

        $this->view('admin/myshop/index', [
            'title' => 'My Shop',
            'settings' => $settings
        ]);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Shop Owner can only update Socials and Review Link
            $allowedKeys = [
                'review_link',
                'social_fb',
                'social_tiktok',
                'social_insta',
                'social_youtube',
                'social_whatsapp',
                'hero_slide_1_link',
                'hero_slide_2_link',
                'hero_slide_3_link',
                'refund_policy_content',
                'terms_conditions_content',
                'privacy_policy_content'
            ];

            foreach ($allowedKeys as $key) {
                if (isset($_POST[$key])) {
                    $this->settingModel->set($key, $_POST[$key]);
                }
            }

            $uploadDir = ROOT_PATH . 'assets/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            for ($i = 1; $i <= 3; $i++) {
                $imageKey = 'hero_slide_' . $i . '_image';
                $removeKey = 'remove_hero_slide_' . $i . '_image';

                if (!empty($_POST[$removeKey])) {
                    $oldUrl = $this->settingModel->get($imageKey);
                    if (!empty($oldUrl)) {
                        $this->deleteFile(basename($oldUrl));
                    }
                    $this->settingModel->set($imageKey, '');
                }

                if (isset($_FILES[$imageKey]) && $_FILES[$imageKey]['error'] === 0) {
                    $fileName = time() . '_hero_' . $i . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', basename($_FILES[$imageKey]['name']));

                    if (move_uploaded_file($_FILES[$imageKey]['tmp_name'], $uploadDir . $fileName)) {
                        $oldUrl = $this->settingModel->get($imageKey);
                        if (!empty($oldUrl)) {
                            $this->deleteFile(basename($oldUrl));
                        }

                        $this->settingModel->set($imageKey, BASE_URL . 'assets/uploads/' . $fileName);
                    }
                }
            }

            // Redirect back with success
            $this->redirect('myShop/index');
        }
    }
}
?>
