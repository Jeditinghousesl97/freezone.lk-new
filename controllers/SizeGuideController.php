<?php
/**
 * Size Guide Controller
 */
require_once 'models/SizeGuide.php';

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
            $imagePath = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                // Fix: Use dynamic path consistent with ProductController
                $targetDir = dirname(__DIR__) . "/assets/uploads/";
                if (!is_dir($targetDir))
                    mkdir($targetDir, 0777, true);

                $fileName = time() . '_sg_' . basename($_FILES['image']['name']);
                $targetFile = $targetDir . $fileName;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                    $imagePath = $fileName;
                }
            }

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