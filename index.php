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
if (!function_exists('app_is_https')) {
    function app_is_https()
    {
        if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            return true;
        }

        if (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443) {
            return true;
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string) $_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        }

        return false;
    }
}

if (!function_exists('app_session_cookie_params')) {
    function app_session_cookie_params()
    {
        return [
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => app_is_https(),
            'httponly' => true,
            'samesite' => 'Lax'
        ];
    }
}

if (function_exists('ini_set')) {
    @ini_set('session.use_only_cookies', '1');
    @ini_set('session.use_strict_mode', '1');
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.cookie_samesite', 'Lax');
}

session_set_cookie_params(app_session_cookie_params());
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        return (string) ($_SESSION['csrf_token'] ?? '');
    }
}

if (!function_exists('csrf_input')) {
    function csrf_input()
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('app_send_security_headers')) {
    function app_send_security_headers()
    {
        header_remove('X-Powered-By');
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header("Permissions-Policy: accelerometer=(), autoplay=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()");
        header("Content-Security-Policy: frame-ancestors 'self'; base-uri 'self'; object-src 'none'");

        if (app_is_https()) {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
        }
    }
}

if (!function_exists('app_request_origin_is_trusted')) {
    function app_request_origin_is_trusted()
    {
        $source = '';

        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            $source = (string) $_SERVER['HTTP_ORIGIN'];
        } elseif (!empty($_SERVER['HTTP_REFERER'])) {
            $source = (string) $_SERVER['HTTP_REFERER'];
        }

        if ($source === '') {
            return false;
        }

        $requestHost = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
        $requestScheme = app_is_https() ? 'https' : 'http';
        $requestPort = isset($_SERVER['SERVER_PORT']) ? (int) $_SERVER['SERVER_PORT'] : ($requestScheme === 'https' ? 443 : 80);
        $sourceParts = parse_url($source);

        if (empty($sourceParts['host'])) {
            return false;
        }

        $sourceHost = strtolower((string) $sourceParts['host']);
        $sourceScheme = strtolower((string) ($sourceParts['scheme'] ?? $requestScheme));
        $sourcePort = isset($sourceParts['port']) ? (int) $sourceParts['port'] : ($sourceScheme === 'https' ? 443 : 80);

        return $sourceHost === $requestHost && $sourceScheme === $requestScheme && $sourcePort === $requestPort;
    }
}

if (!function_exists('app_is_json_request')) {
    function app_is_json_request()
    {
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

        return strpos($contentType, 'application/json') !== false
            || strpos($accept, 'application/json') !== false
            || $requestedWith === 'xmlhttprequest';
    }
}

if (!function_exists('app_is_csrf_exempt')) {
    function app_is_csrf_exempt($request)
    {
        $normalized = trim((string) $request, '/');
        $exemptRoutes = [
            'order/payhereNotify',
            'order/kokoResponse'
        ];

        return in_array($normalized, $exemptRoutes, true);
    }
}

if (!function_exists('app_abort_forbidden')) {
    function app_abort_forbidden($message = 'Forbidden request.')
    {
        http_response_code(403);

        if (app_is_json_request()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
            exit;
        }

        echo $message;
        exit;
    }
}

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

app_send_security_headers();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !app_is_csrf_exempt($request)) {
    $submittedToken = '';
    if (isset($_POST['_csrf'])) {
        $submittedToken = (string) $_POST['_csrf'];
    } elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $submittedToken = (string) $_SERVER['HTTP_X_CSRF_TOKEN'];
    }

    $tokenValid = $submittedToken !== '' && hash_equals(csrf_token(), $submittedToken);
    $originTrusted = app_request_origin_is_trusted();

    if (!$originTrusted && !$tokenValid) {
        app_abort_forbidden('Your session security check failed. Please refresh and try again.');
    }
}

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
