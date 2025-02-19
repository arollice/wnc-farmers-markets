<?php
// Define base paths for file includes
define('PRIVATE_PATH', __DIR__);
define('PROJECT_ROOT', dirname(PRIVATE_PATH));

// Set a web-friendly public path (relative to localhost)
define('PUBLIC_PATH', '/web289/public'); // Adjusted for correct URL paths

define('SHARED_PATH', PUBLIC_PATH . '/shared');

// Define header & footer paths
define('HEADER_FILE', PROJECT_ROOT . '/public/shared/header.php');
define('FOOTER_FILE', PROJECT_ROOT . '/public/shared/footer.php');

// Automatically include database credentials, functions, and connection
require_once PRIVATE_PATH . '/db-credentials.php';
require_once PRIVATE_PATH . '/db-functions.php';
require_once PRIVATE_PATH . '/rollice-ashlee-db-connection.php';
