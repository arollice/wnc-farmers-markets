<?php
include_once('../private/config.php');
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $errors = [];

  // Vendor-specific fields
  $vendor_name        = trim($_POST['vendor_name'] ?? '');
  $vendor_website     = trim($_POST['vendor_website'] ?? '');
  $vendor_description = trim($_POST['vendor_description'] ?? '');

  // Login-specific fields (for the user_account table)
  $username           = trim($_POST['vendor_username'] ?? '');
  $email              = trim($_POST['vendor_email'] ?? '');
  $password           = $_POST['vendor_password'] ?? '';
  $password_confirm   = $_POST['vendor_password_confirm'] ?? '';

  // Optional: Accepted payments
  $accepted_payments  = $_POST['accepted_payments'] ?? [];

  // Validate vendor fields
  if (empty($vendor_name)) {
    $errors[] = "Business Name is required.";
  }
  if (!empty($vendor_website) && !filter_var($vendor_website, FILTER_VALIDATE_URL)) {
    $errors[] = "Please provide a valid URL for the business website.";
  }

  // Validate login fields
  if (empty($username)) {
    $errors[] = "Username is required.";
  }
  if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email is required.";
  }
  if (empty($password) || empty($password_confirm)) {
    $errors[] = "Both password fields are required.";
  } elseif ($password !== $password_confirm) {
    $errors[] = "Passwords do not match.";
  }

  if (!empty($errors)) {
    foreach ($errors as $error) {
      echo "<p>" . htmlspecialchars($error) . "</p>";
    }
    exit;
  }

  // Step 1: Create the vendor record (only vendor-specific fields)
  $vendorData = [
    'vendor_name'        => $vendor_name,
    'vendor_website'     => $vendor_website,
    'vendor_description' => $vendor_description,
    'status'             => 'pending'
  ];

  $vendor = Vendor::register($vendorData);
  if (!$vendor) {
    echo "<p>Vendor registration failed. Please try again.</p>";
    exit;
  }

  // Step 2: Create the user account record and link it to the vendor.
  $userAccountData = [
    'username'  => $username,
    'password'  => $password, // raw password; will be hashed in UserAccount::register()
    'email'     => $email,
    'role'      => 'vendor',
    'vendor_id' => $vendor->vendor_id
  ];

  $userAccount = UserAccount::register($userAccountData);
  if (!$userAccount) {
    echo "<p>User account creation failed. Please try again.</p>";
    exit;
  }

  // Optionally, associate accepted payments with the vendor.
  $vendor->associatePayments($accepted_payments);

  // Set a flash message to indicate success.
  $_SESSION['user_id']   = $userAccount->user_id;
  $_SESSION['username']  = $userAccount->username;
  $_SESSION['role']      = $userAccount->role;
  $_SESSION['success_message'] = "Registration successful! Welcome to your dashboard.";

  // Redirect to the vendor dashboard page.
  header("Location: vendor-dashboard.php?vendor_id=" . $vendor->vendor_id);
  exit;
} else {
  echo "<p>Invalid request method.</p>";
}
