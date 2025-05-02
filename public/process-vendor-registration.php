<?php
include_once('../private/config.php');
include_once('../private/validation.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // CSRF 
  if (! Utils::validateCsrf($_POST['csrf_token'] ?? null)) {
    Utils::setFlashMessage('error', 'Invalid form submission.');
    header("Location: vendor-register.php");
    exit;
  }
  $_POST = Utils::sanitize($_POST);

  $vendor_name        = trim($_POST['vendor_name'] ?? '');
  $vendor_website     = trim($_POST['vendor_website'] ?? '');
  $vendor_description = trim($_POST['vendor_description'] ?? '');

  $username           = trim($_POST['vendor_username'] ?? '');
  $email              = trim($_POST['vendor_email'] ?? '');
  $password           = $_POST['vendor_password'] ?? '';
  $password_confirm   = $_POST['vendor_password_confirm'] ?? '';

  $acceptedPayments  = $_POST['accepted_payments'] ?? [];

  $errors = validateVendorRegistrationFields(
    $vendor_name,
    $vendor_website,
    $username,
    $email,
    $password,
    $password_confirm
  );

  // Duplicate Check
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

  $marketIds        = $_POST['market_ids']        ?? [];

  $errors = array_merge(
    $errors,
    validateMarketSelection($marketIds),
    validatePaymentSelection($acceptedPayments)
  );

  if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['sticky'] = $_POST;
    header("Location: vendor-register.php");
    exit;
  }


  // Register the vendor.
  $vendorData = [
    'vendor_name'        => $vendor_name,
    'vendor_website'     => $vendor_website,
    'vendor_description' => $vendor_description,
    'status'             => 'pending'
  ];

  $vendor = Vendor::register($vendorData);
  if (!$vendor) {
    Utils::setFlashMessage('error', "Vendor registration failed. Please try again.");
    header("Location: vendor-register.php");
    exit;
  }

  // Register the user account.
  $userAccountData = [
    'username'  => $username,
    'password'  => $password, // Raw password hashed in UserAccount::register()
    'email'     => $email,
    'role'      => 'vendor',
    'vendor_id' => $vendor->vendor_id
  ];

  $userAccount = UserAccount::register($userAccountData);
  if (!$userAccount) {
    Utils::setFlashMessage('error', "User account creation failed. Please try again.");
    header("Location: vendor-register.php");
    exit;
  }

  $vendor->associatePayments($acceptedPayments);

  $stmt = $pdo->prepare(
    "INSERT INTO vendor_market (vendor_id, market_id) VALUES (?, ?)"
  );
  foreach ($marketIds as $mId) {
    $stmt->execute([$vendor->vendor_id, $mId]);
  }

  // Set user session details.
  $_SESSION['user_id']   = $userAccount->user_id;
  $_SESSION['username']  = $userAccount->username;
  $_SESSION['role']      = $userAccount->role;

  Utils::setFlashMessage('success', "Registration successful! Welcome to your dashboard.");
  header("Location: vendor-dashboard.php?vendor_id=" . $vendor->vendor_id);

  exit;
} else {
  echo "<p>Invalid request method.</p>";
}
