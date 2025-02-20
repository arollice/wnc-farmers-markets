<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../private/config.php';

try {
  // Test a simple query to confirm the PDO connection using the public getter
  $stmt = DatabaseObject::get_database()->query("SELECT 1");
  if ($stmt->fetch()) {
    echo "Database connection is working!";
  } else {
    echo "Database connection query executed, but no result.";
  }
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}
