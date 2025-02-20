<?php
include_once('../private/config.php');

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
</head>

<body>
  <h2>Admin Dashboard</h2>
  <p>Welcome, <?php echo $_SESSION['username']; ?>!</p>
  <a href="logout.php">Logout</a>
</body>

</html>
