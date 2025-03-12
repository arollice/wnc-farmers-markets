<?php
include_once('../private/config.php');
include_once('../private/validation.php');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $vendor_name        = trim($_POST['vendor_name'] ?? '');
  $vendor_website     = trim($_POST['vendor_website'] ?? '');
  $vendor_description = trim($_POST['vendor_description'] ?? '');

  $username           = trim($_POST['vendor_username'] ?? '');
  $email              = trim($_POST['vendor_email'] ?? '');
  $password           = $_POST['vendor_password'] ?? '';
  $password_confirm   = $_POST['vendor_password_confirm'] ?? '';

  $accepted_payments  = $_POST['accepted_payments'] ?? [];

  // Validate vendor registration fields using the validation function.
  $errors = validateVendorRegistrationFields($vendor_name, $vendor_website, $username, $email, $password, $password_confirm);

  // Duplicate Check: Verify that the username and email are not already in use.
  $pdo = DatabaseObject::get_database();
  if (empty($errors)) {
    $stmt = $pdo->prepare("SELECT username, email FROM user_account WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      if ($row['username'] === $username) {
        $errors[] = "The username '$username' is already taken.";
      }
      if ($row['email'] === $email) {
        $errors[] = "The email '$email' is already in use.";
      }
    }
  }

  // If any errors exist, set them in session and redirect back.
  if (!empty($errors)) {
    // Save submitted data (except passwords) for sticky form fields.
    $_SESSION['sticky'] = [
      'vendor_name'        => $vendor_name,
      'vendor_username'    => $username,
      'vendor_email'       => $email,
      'vendor_website'     => $vendor_website,
      'vendor_description' => $vendor_description,
      'market_ids'         => $_POST['market_ids'] ?? [],
      'accepted_payments'  => $accepted_payments,
    ];
    $_SESSION['error_message'] = '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>';
    header("Location: vendor-register.php");
    exit;
  }

  $vendorData = [
    'vendor_name'        => $vendor_name,
    'vendor_website'     => $vendor_website,
    'vendor_description' => $vendor_description,
    'status'             => 'pending'
  ];

  $vendor = Vendor::register($vendorData);
  if (!$vendor) {
    $_SESSION['error_message'] = "Vendor registration failed. Please try again.";
    header("Location: vendor-register.php");
    exit;
  }

  $userAccountData = [
    'username'  => $username,
    'password'  => $password, // raw password; will be hashed in UserAccount::register()
    'email'     => $email,
    'role'      => 'vendor',
    'vendor_id' => $vendor->vendor_id
  ];

  $userAccount = UserAccount::register($userAccountData);
  if (!$userAccount) {
    $_SESSION['error_message'] = "User account creation failed. Please try again.";
    header("Location: vendor-register.php");
    exit;
  }

  // Optionally, associate accepted payments with the vendor.
  $vendor->associatePayments($accepted_payments);

  // Insert the market associations if any were selected.
  if (isset($_POST['market_ids']) && is_array($_POST['market_ids'])) {
    $stmt = $pdo->prepare("INSERT INTO vendor_market (vendor_id, market_id) VALUES (?, ?)");
    foreach ($_POST['market_ids'] as $market_id) {
      $stmt->execute([$vendor->vendor_id, $market_id]);
    }
  }

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
