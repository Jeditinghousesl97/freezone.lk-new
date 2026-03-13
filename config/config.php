<?php

$is_local = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');

if ($is_local) {
    // === LOCAL DEVELOPMENT SETTINGS ===
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306'); // XAMPP Default Port (Updated to yours)
    define('DB_NAME', 'fff');
    define('DB_USER', 'root');
    define('DB_PASS', '');

    // Base URL for links (folder name on your laptop)
    define('BASE_URL', '/');

} else {
    // === PRODUCTION SETTINGS (domain) ===
    define('DB_HOST', 'sdb-85.hosting.stackcp.net');
    define('DB_PORT', '3306'); // Standard MySQL Port for Hosting
    define('DB_NAME', 'freezone-35303938a36f');
    define('DB_USER', 'freezone-35303938a36f');
    define('DB_PASS', 'ne-35303938a');

    // Base URL for links (deployment folder on server)
    define('BASE_URL', '/');
}

// Global helper for Base Path (Root Directory)
define('ROOT_PATH', dirname(__DIR__) . '/');
?>