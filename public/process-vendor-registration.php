<?php
include_once('../private/config.php');
include_once('../private/validation.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "<p>Invalid request method.</p>";
  exit;
}

// CSRF & sanitize
if (! Utils::validateCsrf($_POST['csrf_token'] ?? null)) {
  Utils::setFlashMessage('error', 'Invalid form submission.');
  header("Location: vendor-register.php");
  exit;
}
$_POST = Utils::sanitize($_POST);

// Gather + validate inputs
$vendor_name        = trim($_POST['vendor_name']        ?? '');
$vendor_website     = trim($_POST['vendor_website']     ?? '');
$vendor_description = trim($_POST['vendor_description'] ?? '');
$username           = trim($_POST['vendor_username']    ?? '');
$email              = trim($_POST['vendor_email']       ?? '');
$password           = $_POST['vendor_password']        ?? '';
$password_confirm   = $_POST['vendor_password_confirm'] ?? '';
$acceptedPayments   = $_POST['accepted_payments']      ?? [];
$marketIds          = $_POST['market_ids']             ?? [];

$errors = validateVendorRegistrationFields(
  $vendor_name,
  $vendor_website,
  $username,
  $email,
  $password,
  $password_confirm
);
$errors = array_merge(
  $errors,
  validateMarketSelection($marketIds),
  validatePaymentSelection($acceptedPayments)
);

// Duplicate username/email check
if (empty($errors)) {
  $stmt = $pdo->prepare("SELECT username,email FROM user_account WHERE username=? OR email=?");
  $stmt->execute([$username, $email]);
  while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    if ($row['username'] === $username) $errors[] = "Username '$username' already taken.";
    if ($row['email']    === $email)    $errors[] = "Email '$email' already in use.";
  }
}

if (!empty($errors)) {
  $_SESSION['errors'] = $errors;
  $_SESSION['sticky'] = $_POST;
  header("Location: vendor-register.php");
  exit;
}

// All clear—do the inserts inside a transaction
$pdo->beginTransaction();
try {
  // Insert vendor
  $stmt = $pdo->prepare("
    INSERT INTO vendor (vendor_name, vendor_website, vendor_description, status)
    VALUES (?, ?, ?, 'pending')
  ");
  $stmt->execute([$vendor_name, $vendor_website, $vendor_description]);
  $vendor_id = (int)$pdo->lastInsertId();

  // Insert user_account (including the new vendor_id)
  $stmt = $pdo->prepare("
    INSERT INTO user_account
      (username, password_hash, email, role, vendor_id)
    VALUES (?, ?, ?, 'vendor', ?)
  ");
  $password_hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt->execute([$username, $password_hash, $email, $vendor_id]);
  $user_id = (int)$pdo->lastInsertId();

  // Link payments
  $vendorObj = Vendor::find_by_id($vendor_id);
  $vendorObj->associatePayments($acceptedPayments);

  // Link markets
  $stmt = $pdo->prepare("INSERT INTO vendor_market (vendor_id, market_id) VALUES (?, ?)");
  foreach ($marketIds as $mId) {
    $stmt->execute([$vendor_id, $mId]);
  }

  $pdo->commit();
} catch (Exception $e) {
  $pdo->rollBack();
  Utils::setFlashMessage('error', "Registration failed: " . $e->getMessage());
  header("Location: vendor-register.php");
  exit;
}

// Set session and redirect into new dashboard
$_SESSION['user_id']   = $user_id;
$_SESSION['username']  = $username;
$_SESSION['role']      = 'vendor';
$_SESSION['vendor_id'] = $vendor_id;

Utils::setFlashMessage('success', "Registration successful! Welcome to your dashboard.");
header("Location: vendor-dashboard.php");
exit;
