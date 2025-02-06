<?php
include_once('db-credentials.php');
include_once('db-functions.php');

// Enable error reporting (Remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session (if user authentication is required)
session_start();

try {
  // Establish PDO database connection instead of mysqli to prevent any injection attacks
  $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exception-based error handling
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES => false // Use real prepared statements for security
  ]);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}
