<?php
// Define base paths for file includes
define('PRIVATE_PATH', __DIR__);
define('PROJECT_ROOT', dirname(PRIVATE_PATH));

// Set session cookie parameters (this should already be in your config)
session_set_cookie_params(0, '/');

// Set the session save path to your custom folder.
// PROJECT_ROOT is defined as the parent directory of your private folder.
//session_save_path(PROJECT_ROOT . '/sessions');

// Start the session
session_start();

// Set a web-friendly public path (relative to localhost)
define('PUBLIC_PATH', '/web289/public');

define('SHARED_PATH', PRIVATE_PATH . '/shared');

define('HEADER_FILE', SHARED_PATH . '/header.php');
define('FOOTER_FILE', SHARED_PATH . '/footer.php');

define('UPLOADS_PATH', PROJECT_ROOT . '/public/uploads');


require_once PRIVATE_PATH . '/db-credentials.php';

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

  $baseDir = __DIR__ . '/classes/';

  // Convert the class name to lowercase
  $file = $baseDir . strtolower($class) . '.class.php';

  if (file_exists($file)) {
    require_once $file;
  }
});

// Set the PDO connection for the DatabaseObject class
DatabaseObject::set_database($pdo);
