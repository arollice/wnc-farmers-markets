<?php
// test_connection.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../private/config.php';
//require_once '../private/classes/databaseobject.class.php';


DatabaseObject::set_database($pdo);

// Test connection
try {
  $stmt = DatabaseObject::get_database()->query("SELECT 1");
  if ($stmt->fetch()) {
    echo "Database connection is working!";
  } else {
    echo "Database connection query executed, but no result.";
  }
} catch (PDOException $e) {
  echo "Error: " . $e->getMessage();
}

echo "PHP is working and errors are visible.";
//phpinfo();
