<?php
/**
 * Base Controller
 * 
 * This is the "Parent" class for all other Controllers.
 * Ideally, it holds shared logic like "rendering a view".
 * 
 * Think of this as the basic toolset every controller gets.
 */
class BaseController
{

    /**
     * Render a View
     * 
     * This function is used to load the HTML files (Views).
     * 
     * @param string $viewPath The path to the view file (e.g., 'admin/dashboard')
     * @param array $data Data to pass to the view (e.g., ['title' => 'Dashboard'])
     */
    protected function view($viewPath, $data = [])
    {
        // Extract array keys as variables
        // If we pass ['title' => 'Home'], the view will have a variable $title = 'Home'.
        extract($data);

        $fullPath = 'views/' . $viewPath . '.php';

        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            echo "Error: View file '$viewPath' not found.";
        }
    }

    /**
     * Redirect
     * Helper to send the user to a new URL.
     */
    protected function redirect($url)
    {
        // Use BASE_URL from config.php
        header("Location: " . BASE_URL . $url);
        exit;
    }

    /**
     * Safe File Deletion Helper 
     * Filesystem hygiene to prevent orphan files.
     * 
     * @param string $filename The basename of the file (e.g. '123_abc.jpg')
     */
    protected function deleteFile($filename)
    {
        // Basic Validation
        if (empty($filename)) {
            return;
        }

        // Construct Absolute Path
        // ROOT_PATH is defined in config.php
        $filePath = ROOT_PATH . 'assets/uploads/' . $filename;

        // Safety Checks & Deletion
        try {
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
            }
        } catch (Exception $e) {
            // Fail silently. Database integrity is more important than disk space here.
            // We do NOT want to crash the request if permission fails.
        }
    }

}
?>