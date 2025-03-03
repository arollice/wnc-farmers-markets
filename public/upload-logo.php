<?php
session_start();
include_once('../private/config.php');


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the user is logged in as a vendor.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
  header('Location: login.php');
  exit;
}

// Check if a file was uploaded without error.
if (!isset($_FILES['vendor_logo']) || $_FILES['vendor_logo']['error'] !== UPLOAD_ERR_OK) {
  error_log("File upload error: " . $_FILES['vendor_logo']['error']);
  $_SESSION['error_message'] = "There was an error uploading your file. Please try again.";
  header("Location: vendor-dashboard.php");
  exit;
}

// Validate file type.
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($_FILES['vendor_logo']['type'], $allowed_types)) {
  error_log("Invalid file type: " . $_FILES['vendor_logo']['type']);
  $_SESSION['error_message'] = "Only JPG, PNG, and GIF files are allowed.";
  header("Location: vendor-dashboard.php");
  exit;
}

// Validate file size (max 2MB in this example).
$maxSize = 2 * 1024 * 1024;
if ($_FILES['vendor_logo']['size'] > $maxSize) {
  error_log("File too large: " . $_FILES['vendor_logo']['size']);
  $_SESSION['error_message'] = "The file is too large. Maximum allowed size is 2MB.";
  header("Location: vendor-dashboard.php");
  exit;
}

// Define the destination directory and ensure it exists.
$target_dir = UPLOADS_PATH;
if (!is_dir($target_dir)) {
  if (!mkdir($target_dir, 0755, true)) {
    error_log("Failed to create directory: " . $target_dir);
    $_SESSION['error_message'] = "Server error. Please try again later.";
    header("Location: vendor-dashboard.php");
    exit;
  }
}

// Retrieve the vendor ID from the logged-in user's account.
$userAccount = UserAccount::find_by_id($_SESSION['user_id']);
if (!$userAccount || empty($userAccount->vendor_id)) {
  error_log("Unable to retrieve vendor information for user_id: " . $_SESSION['user_id']);
  $_SESSION['error_message'] = "There was an error retrieving your account information.";
  header("Location: vendor-dashboard.php");
  exit;
}
$vendor_id = $userAccount->vendor_id;

// Generate a unique file name.
$extension = strtolower(pathinfo($_FILES["vendor_logo"]["name"], PATHINFO_EXTENSION));
$unique_name = "vendor_" . $vendor_id . "_" . time() . "_" . uniqid() . "." . $extension;
$target_file = $target_dir . $unique_name;

// Attempt to move the uploaded file.
if (!move_uploaded_file($_FILES["vendor_logo"]["tmp_name"], $target_file)) {
  error_log("Failed to move uploaded file to: " . $target_file);
  $_SESSION['error_message'] = "There was an error uploading your file. Please try again.";
  header("Location: vendor-dashboard.php");
  exit;
}

// Retrieve the existing vendor record.
$existing_vendor = Vendor::findVendorById($vendor_id);
if (!$existing_vendor) {
  error_log("Could not find vendor record for vendor_id: " . $vendor_id);
  $_SESSION['error_message'] = "Server error, please contact support.";
  header("Location: vendor-dashboard.php");
  exit;
}

// Populate a Vendor object with the existing data.
$vendorObj = new Vendor();
$vendorObj->vendor_id = $vendor_id;
$vendorObj->vendor_name = $existing_vendor['vendor_name'];
$vendorObj->vendor_website = $existing_vendor['vendor_website'];
$vendorObj->vendor_description = $existing_vendor['vendor_description'];
$vendorObj->status = $existing_vendor['status'];

// Set the new logo filename.
$vendorObj->vendor_logo = $unique_name;

// Update the vendor record.
if (!$vendorObj->save()) {
  error_log("Failed to update vendor record with logo: " . $unique_name);
  $_SESSION['error_message'] = "Logo uploaded, but there was an error updating your profile. Please contact support.";
  header("Location: vendor-dashboard.php");
  exit;
}

// Set a success message and redirect to the vendor dashboard.
$_SESSION['success_message'] = "Logo uploaded successfully.";
header("Location: vendor-dashboard.php?vendor_id=" . $vendor_id);
exit;
