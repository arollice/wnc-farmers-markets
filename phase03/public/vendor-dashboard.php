<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
  header('Location: login.php');
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Vendor Dashboard</title>
</head>

<body>
  <h2>Welcome, <?php echo $_SESSION['username']; ?>!</h2>
  <p>This is the vendor dashboard.</p>
  <a href="logout.php">Logout</a>
</body>

</html>
