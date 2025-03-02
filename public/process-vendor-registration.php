<?php
include_once('../private/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $errors = [];


  $vendor_name             = trim($_POST['vendor_name'] ?? '');
  $vendor_username         = trim($_POST['vendor_username'] ?? '');
  $vendor_website          = trim($_POST['vendor_website'] ?? '');
  $vendor_description      = trim($_POST['vendor_description'] ?? '');
  $vendor_password         = $_POST['vendor_password'] ?? '';
  $vendor_password_confirm = $_POST['vendor_password_confirm'] ?? '';
  $accepted_payments       = $_POST['accepted_payments'] ?? [];

  if (empty($vendor_name)) {
    $errors[] = "Business Name is required.";
  }
  if (empty($vendor_username)) {
    $errors[] = "Username is required.";
  }
  if (empty($vendor_password) || empty($vendor_password_confirm)) {
    $errors[] = "Both password fields are required.";
  } elseif ($vendor_password !== $vendor_password_confirm) {
    $errors[] = "Passwords do not match.";
  }

  if (!empty($vendor_website) && !filter_var($vendor_website, FILTER_VALIDATE_URL)) {
    $errors[] = "Please provide a valid URL for the business website.";
  }

  if (!empty($errors)) {
    foreach ($errors as $error) {
      echo "<p>" . htmlspecialchars($error) . "</p>";
    }
    exit;
  }

  $registrationData = [
    'vendor_name'        => $vendor_name,
    'vendor_username'    => $vendor_username,
    'vendor_password'    => $vendor_password,  // Will be hashed in the register() method
    'vendor_website'     => $vendor_website,
    'vendor_description' => $vendor_description,
  ];

  $vendor = Vendor::register($registrationData);
  if (!$vendor) {
    echo "<p>Vendor registration failed. Please try again.</p>";
    exit;
  }

  $vendor->associatePayments($accepted_payments);

  // Redirect to vendor details (profile preview) page.
  header("Location: vendor-details.php?vendor_id=" . $vendor->vendor_id);
  exit;
} else {
  echo "<p>Invalid request method.</p>";
}
