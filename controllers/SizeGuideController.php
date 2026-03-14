<?php
/**
 * Size Guide Controller
 */
require_once 'models/SizeGuide.php';
require_once 'helpers/ImageHelper.php';

class SizeGuideController extends BaseController
{

    private $model;

    public function __construct()
    {
        $this->model = new SizeGuide();
    }

    public function index()
    {
        $guides = $this->model->getAll();
        $this->view('admin/sizeguides/index', [
            'title' => 'Size Guides',
            'guides' => $guides
        ]);
    }

    public function add()
    {
        $this->view('admin/sizeguides/form', [
            'title' => 'Add Size Guide',
            'mode' => 'add'
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';

            // Image Upload
            $imagePath = isset($_FILES['image']) ? ImageHelper::storeUploadedFile($_FILES['image'], 'sizeguide') : '';

            if (
                $this->model->create([
                    'name' => $name,
                    'image_path' => $imagePath
                ])
            ) {
                $this->redirect('sizeGuide/index');
            } else {
                echo "Error adding size guide.";
            }
        }
    }

    public function delete($id)
    {
        // Image Hygiene
        $guide = $this->model->getById($id);
        if ($guide && !empty($guide['image_path'])) {
            $this->deleteFile($guide['image_path']);
        }

        $this->model->delete($id);
        $this->redirect('sizeGuide/index');
    }

    /**
     * API: Get Size Guides as JSON
     */
    public function get_json()
    {
        header('Content-Type: application/json');
        $guides = $this->model->getAll();
        echo json_encode($guides);
        exit;
    }

}
?>
