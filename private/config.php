<?php
// Define base paths for file includes
define('PRIVATE_PATH', __DIR__);
define('PROJECT_ROOT', dirname(PRIVATE_PATH));

// Set a web-friendly public path (relative to localhost)
define('PUBLIC_PATH', '/web289/public');

// Update the shared folder path to point to the new location inside private
define('SHARED_PATH', PRIVATE_PATH . '/shared');

// Define header & footer paths using the new shared folder location
define('HEADER_FILE', SHARED_PATH . '/header.php');
define('FOOTER_FILE', SHARED_PATH . '/footer.php');

// Include database credentials and functions
require_once PRIVATE_PATH . '/db-credentials.php';
require_once PRIVATE_PATH . '/db-functions.php';

// Include the new DatabaseObject class file (located in the classes folder)
require_once PRIVATE_PATH . '/classes/databaseobject.class.php';

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

spl_autoload_register(function ($class) {
  // Adjust the base directory according to your file structure
  $baseDir = __DIR__ . '/classes/';

  // Convert the class name to lowercase (if your files are all lowercase)
  $file = $baseDir . strtolower($class) . '.class.php';

  if (file_exists($file)) {
    require_once $file;
  }
});

// Set the PDO connection for the DatabaseObject class
DatabaseObject::set_database($pdo);
