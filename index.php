<?php
/**
 * Main Entry Point (The "Traffic Cop")
 * 
 * Every request to the website starts here.
 * This file's job is to:
 * 1. Start a "Session" (memory) for the user.
 * 2. Load necessary configuration files.
 * 3. Look at the URL to see what the user wants.
 * 4. Send the user to the right "Controller" (Logic Handler).
 */

// 1. Start Session
// This allows us to remember who is logged in across different pages.
session_start();

// 2. Load the Database Configuration
require_once 'config/db.php';

// 2.1 Load Base Classes (Core)
// We need these to be available before any Controller or Model is used.
require_once 'controllers/BaseController.php';
require_once 'models/BaseModel.php';

// 3. Simple Router Logic
// We get the URL path. If it's empty, we assume they want the 'home' page.
// Example: http://localhost/Ecom-CMS/login -> $request = 'login'
$request = isset($_GET['url']) ? $_GET['url'] : 'home'; // 'home' is default

// Remove slashes from the end (e.g. login/ -> login)
$request = rtrim($request, '/');

// Break the URL into parts. 
// Example: products/view/12 -> ['products', 'view', '12']
$params = explode('/', $request);

// The first part identifies the Controller (e.g., 'products')
$controllerName = isset($params[0]) ? $params[0] : 'home';
$actionName = isset($params[1]) ? $params[1] : 'index'; // Default action is 'index'

// Check if it's a special Admin route
// If URL starts with 'admin', we might handle it differently later.
// For now, let's keep it simple.

// 4. Route to the Controller
// We follow a convention: 'login' -> LoginController
// 'products' -> ProductController
// 'home' -> HomeController

// Capitalize first letter (e.g. 'home' -> 'Home')
$controllerClass = ucfirst($controllerName) . 'Controller';

// Path to the controller file
$controllerFile = 'controllers/' . $controllerClass . '.php';

// Check if the controller file exists
if (file_exists($controllerFile)) {
    require_once $controllerFile;

    // Create an instance of the controller
    $controller = new $controllerClass();

    // Check if the method (action) exists in the controller
    if (method_exists($controller, $actionName)) {
        // Call the action, creating a response
        // We pass the remaining params (like ID) if needed, but for now just call it.
        // Simple version: just call the function.
        call_user_func_array([$controller, $actionName], array_slice($params, 2));
    } else {
        // Action not found
        http_response_code(404);
        require_once 'views/errors/404.php';
    }
} else {
    // Controller not found
    // If it's just the root '/', we might not have a HomeController yet.
    // For testing, let's just show a welcome message if it's home.
    if ($controllerName == 'home') {
        echo "<h1>Welcome to Ecom-CMS</h1><p>Routing is working!</p>";
    } else {
        http_response_code(404);
        require_once 'views/errors/404.php';
    }
}
?>