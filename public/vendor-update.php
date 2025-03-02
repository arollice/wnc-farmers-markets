<?php
include_once('../private/config.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
  header('Location: login.php');
  exit;
}

$vendor_id = $_SESSION['user_id'];
$vendor = Vendor::find_by_id($vendor_id); // Ensure this returns your Vendor object

$action = $_POST['action'] ?? '';
$messages = []; // Array to store success/error messages

switch ($action) {
  case 'update_description':
    $new_description = trim($_POST['vendor_description'] ?? '');
    $vendor->vendor_description = $new_description;
    if ($vendor->save()) {
      $messages[] = "Description updated successfully.";
    } else {
      $messages[] = "Failed to update description.";
    }
    break;

  case 'update_website':
    $new_website = trim($_POST['vendor_website'] ?? '');
    if (!empty($new_website) && !filter_var($new_website, FILTER_VALIDATE_URL)) {
      $messages[] = "Invalid URL provided for website.";
    } else {
      $vendor->vendor_website = $new_website;
      if ($vendor->save()) {
        $messages[] = "Website updated successfully.";
      } else {
        $messages[] = "Failed to update website.";
      }
    }
    break;

  case 'update_payments':
    $accepted_payments = $_POST['accepted_payments'] ?? [];
    if (Currency::associateVendorPayments($vendor_id, $accepted_payments)) {
      $messages[] = "Payment methods updated successfully.";
    } else {
      $messages[] = "Failed to update payment methods.";
    }
    break;

  case 'upload_logo':
    if (isset($_FILES['vendor_logo']) && $_FILES['vendor_logo']['error'] == 0) {
      $fileMessages = [];

      // Validate the file is a valid image.
      $check = getimagesize($_FILES['vendor_logo']['tmp_name']);
      if ($check === false) {
        $fileMessages[] = "Uploaded file is not a valid image.";
      }

      // Check file size (2MB limit).
      if ($_FILES['vendor_logo']['size'] > 2 * 1024 * 1024) {
        $fileMessages[] = "File is too large. Maximum allowed size is 2MB.";
      }

      // Validate allowed extensions.
      $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
      $ext = strtolower(pathinfo($_FILES['vendor_logo']['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowed_extensions)) {
        $fileMessages[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
      }

      // If there are any validation errors, merge them and break.
      if (!empty($fileMessages)) {
        $messages = array_merge($messages, $fileMessages);
        break;
      }

      // Generate a unique filename using vendor_id and timestamp.
      $newFileName = "vendor_{$vendor_id}_" . time() . "." . $ext;
      $destination = "../vendor-logo-uploads/" . $newFileName;

      if (move_uploaded_file($_FILES['vendor_logo']['tmp_name'], $destination)) {
        // Store a relative path for web access.
        $relativePath = "/vendor-logo-uploads/" . $newFileName;
        $vendor->vendor_logo = $relativePath;
        if ($vendor->save()) {
          $messages[] = "Logo uploaded and updated successfully.";
        } else {
          $messages[] = "Logo uploaded but failed to update record.";
        }
      } else {
        $messages[] = "Failed to upload logo.";
      }
    } else {
      $messages[] = "No logo file selected or file error.";
    }
    break;

  case 'change_password':
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Verify current password using Vendor's verifyPassword() method.
    if (!$vendor->verifyPassword($current_password)) {
      $messages[] = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
      $messages[] = "New passwords do not match.";
    } else {
      $vendor->vendor_password = password_hash($new_password, PASSWORD_DEFAULT);
      if ($vendor->save()) {
        $messages[] = "Password changed successfully.";
      } else {
        $messages[] = "Failed to change password.";
      }
    }
    break;

  default:
    $messages[] = "No valid action specified.";
    break;
}

// Pass messages back to the dashboard via the session
$_SESSION['update_messages'] = $messages;
header("Location: vendor-dashboard.php");
exit;


//If issues occur make sure post_max_size (8M) is same as upload_max_filesize = 32M, can adjust in php.ini
