<?php
include_once('../private/config.php');
include_once('../private/validation.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

include_once HEADER_FILE;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Admin Home</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <main class="admin-chooser">
    <header class="dashboard-header">
      <h2>Admin Dashboard</h2>
      <p>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</p>
      <a href="logout.php">Logout</a>
    </header>

    <section class="admin-choices">
      <a href="admin-manage-admins.php" class="admin-card">
        <h3>Manage Admins</h3>
        <p>Add, edit, or remove administrator accounts.</p>
      </a>

      <a href="admin-manage-vendors.php" class="admin-card">
        <h3>Manage Vendors</h3>
        <p>Approve, edit, or delete vendor profiles.</p>
      </a>

      <a href="admin-manage-markets.php" class="admin-card">
        <h3>Manage Markets</h3>
        <p>Modify market information</p>
      </a>
    </section>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
