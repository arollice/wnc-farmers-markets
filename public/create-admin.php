<?php
include_once('../private/config.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Only allow admin users to access this page.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

// Process form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = [
    'username'   => $_POST['admin_name'] ?? '',
    'password'   => $_POST['admin_password'] ?? '',
    'email'      => $_POST['admin_email'] ?? '',
    'role'       => 'admin',
    'vendor_id'  => null
  ];

  // Use the UserAccount class's register() method.
  $new_admin = UserAccount::register($data);
  if ($new_admin) {
    $_SESSION['success_message'] = "Admin account created successfully.";
  } else {
    $_SESSION['error_message'] = "Error creating admin account.";
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
    <?php
    // Display any session messages.
    if (isset($_SESSION['error_message'])) {
      echo "<div>" . htmlspecialchars($_SESSION['error_message']) . "</div>";
      unset($_SESSION['error_message']);
    }
    if (isset($_SESSION['success_message'])) {
      echo "<div>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
      unset($_SESSION['success_message']);
    }
    ?>
    <form action="create-admin.php" method="post">
      <div>
        <label for="admin_name">Admin Name:</label>
        <input type="text" name="admin_name" id="admin_name" required>
      </div>
      <div>
        <label for="admin_email">Email:</label>
        <input type="email" name="admin_email" id="admin_email" required>
      </div>
      <div>
        <label for="admin_password">Password:</label>
        <input type="password" name="admin_password" id="admin_password" required>
      </div>
      <button type="submit">Create Admin</button>
    </form>
    <p><a href="admin.php">Return to Dashboard</a></p>
  </main>
</body>
<?php include_once FOOTER_FILE; ?>

</html>
