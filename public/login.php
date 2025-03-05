<?php
session_start();
include_once __DIR__ . '/../private/config.php';
include HEADER_FILE;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT user_id, username, password_hash, role FROM user_account WHERE username = ? LIMIT 1");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  // Use password_verify() to compare the raw password with the hashed version.
  if ($user && password_verify($password, $user['password_hash'])) {
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];

    // Redirect based on role.
    if ($user['role'] === 'admin') {
      header('Location: admin.php');
    } elseif ($user['role'] === 'vendor') {
      header('Location: vendor-dashboard.php');
    } else {
      header('Location: index.php');
    }
    exit;
  } else {
    $error = "Invalid username or password";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Login</title>
</head>

<body>
  <h2>Login</h2>
  <?php
  if (!empty($error)) {
    echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>";
  }
  // Display the flash message if it exists.
  if (isset($_SESSION['success_message'])) {
    echo "<p style='color:green;'>" . htmlspecialchars($_SESSION['success_message']) . "</p>";
    unset($_SESSION['success_message']);
  }
  ?>
  <form method="POST">
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <button type="submit">Login</button>
  </form>
</body>

</html>
<?php include FOOTER_FILE;
?>
