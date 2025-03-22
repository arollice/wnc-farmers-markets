<?php
include_once('../private/config.php');

$sticky = $_SESSION['sticky'] ?? [];

// Only allow admin users to access this page.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

// Process form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $adminName = $_POST['admin_name'] ?? '';
  $adminEmail = $_POST['admin_email'] ?? '';
  $adminPassword = $_POST['admin_password'] ?? '';
  $adminPasswordConfirm = $_POST['admin_password_confirm'] ?? '';

  // Check if passwords match.
  if ($adminPassword !== $adminPasswordConfirm) {
    Utils::setFlashMessage('error', "Passwords do not match.");
    header("Location: create-admin.php");
    exit;
  }

  $data = [
    'username'  => $adminName,
    'password'  => $adminPassword,
    'email'     => $adminEmail,
    'role'      => 'admin',
    'vendor_id' => null
  ];

  // Use the UserAccount class's register() method.
  $new_admin = UserAccount::register($data);
  if ($new_admin) {
    Utils::setFlashMessage('success', "Admin account created successfully.");
  } else {
    Utils::setFlashMessage('error', "Error creating admin account.");
  }
  header("Location: admin.php");
  exit;
}

include_once HEADER_FILE;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Create New Admin</title>
</head>

<body>
  <main>
    <h2>Add New Admin</h2>

    <?php Utils::displayFlashMessages(); ?>

    <form action="create-admin.php" method="post">
      <label for="admin_name">Username:</label>
      <input type="text" id="admin_name" name="admin_name" required
        value="<?= htmlspecialchars($sticky['admin_name'] ?? '') ?>">

      <label for="admin_email">Email:</label>
      <input type="email" id="admin_email" name="admin_email" required
        value="<?= htmlspecialchars($sticky['admin_email'] ?? '') ?>">

      <label for="admin_password">Password:</label>
      <input type="password" id="admin_password" name="admin_password" required
        minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
        placeholder="At least 8 characters, including uppercase, lowercase & numbers">

      <label for="admin_password_confirm">Confirm Password:</label>
      <input type="password" id="admin_password_confirm" name="admin_password_confirm" required minlength="8">

      <button type="submit">Create Admin</button>
    </form>
    <p><a href="admin.php">Return to Dashboard</a></p>
  </main>
</body>
<?php
unset($_SESSION['sticky']);
include_once FOOTER_FILE; ?>

</html>
