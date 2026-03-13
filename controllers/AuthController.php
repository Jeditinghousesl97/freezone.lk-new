<?php
/**
 * AuthController
 * 
 * Handles Login, Logout, and Session management.
 */
require_once 'models/User.php';

class AuthController extends BaseController
{

    // Default action: Redirect to login
    public function index()
    {
        $this->redirect('auth/login');
    }

    /**
     * Show Login Page
     */
    public function login()
    {
        // If already logged in, go to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('admin/dashboard');
            return;
        }

        // Load the view file: views/admin/login.php
        // We pass 'title' to be used in the HTML <title> tag

        // Fetch Settings for Logo and Name
        require_once 'models/Setting.php';
        $settingModel = new Setting();
        $settings = $settingModel->getAllPairs();

        $this->view('admin/login', [
            'title' => 'Login - EcomCMS',
            'settings' => $settings
        ]);
    }

    /**
     * Process Login Form (POST request)
     */
    public function authenticate()
    {
        // Check if form was submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get inputs
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Validate Logic regarding Empty Fields
            if (empty($username) || empty($password)) {
                // Redirect back with error
                // In a real app we'd use Flash Messages, for now using URL param
                $this->redirect('auth/login?error=empty_fields');
                return;
            }

            // Connect to Model
            $userModel = new User();
            $user = $userModel->login($username, $password);

            if ($user) {
                // SUCCESS: Login verified
                // Store user info in Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                // Redirect based on role (Developer vs Shop Owner)
                // Both go to the main dashboard now
                $this->redirect('admin/dashboard');

                /* 
                if ($user['role'] === 'developer') {
                    $this->redirect('admin/dashboard');
                } else {
                    $this->redirect('shop/dashboard');
                }
                */
            } else {
                // FAILURE: Wrong credentials
                $this->redirect('auth/login?error=invalid_credentials');
            }
        } else {
            // If someone tries to visit this URL directly without POST
            $this->redirect('auth/login');
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_destroy();
        $this->redirect('auth/login');
    }
}
?>