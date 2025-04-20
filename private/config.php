<?php
ob_start();
// Define base paths for file includes
define('PRIVATE_PATH', __DIR__);
define('PROJECT_ROOT', dirname(PRIVATE_PATH));

// This is the web-friendly path (for URLs)
// If your public folder is at /web289/public on your server, this is correct:
define('PUBLIC_PATH', '/web289/public');

// For file includes, build the path starting at the project root:
define('SHARED_PATH', PROJECT_ROOT . '/public/shared');
define('HEADER_FILE', SHARED_PATH . '/header.php');
define('FOOTER_FILE', SHARED_PATH . '/footer.php');
define('UPLOADS_PATH', PROJECT_ROOT . '/public/uploads');

require_once PRIVATE_PATH . '/db-credentials.php';

// Register the autoloader first.
spl_autoload_register(function ($class) {
  $baseDir = __DIR__ . '/classes/';
  // Convert the class name to lowercase (if that matches your naming scheme)
  $file = $baseDir . strtolower($class) . '.class.php';
  if (file_exists($file)) {
    require_once $file;
  }
});

// Optionally, explicitly include specific classes if needed.
require_once PRIVATE_PATH . '/classes/databaseobject.class.php';
require_once PRIVATE_PATH . '/classes/sessionmanager.class.php';

// Create the PDO connection
try {
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
  ]);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Set the PDO connection for the DatabaseObject class
DatabaseObject::set_database($pdo);

// Initialize the session manager (this registers your custom session handler and calls session_start())
$sessionManager = new SessionManager($pdo);

// Breadcrumb initialization with exclusion of certain URLs
if (!isset($_SESSION['breadcrumbs'])) {
  $_SESSION['breadcrumbs'] = [];
}

$current_url = $_SERVER['REQUEST_URI'];

// Check if the current URL contains "fetch-regions" (case-sensitive)
if (strpos($current_url, 'fetch-regions') === false) {
  // If the current URL is not already in the breadcrumbs, or if it's been seen before, update the chain
  $found_key = array_search($current_url, $_SESSION['breadcrumbs']);
  if ($found_key !== false) {
    // Keep only the breadcrumbs up to the current URL
    $_SESSION['breadcrumbs'] = array_slice($_SESSION['breadcrumbs'], 0, $found_key + 1);
  } else {
    // Append the current URL if it's new
    $_SESSION['breadcrumbs'][] = $current_url;
  }
}
