<?php
include_once __DIR__ . '/../private/config.php';
include_once __DIR__ . '/../private/validation.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username'] ?? '');
  $password = trim($_POST['password'] ?? '');

  $errors = validateLoginFields($username, $password);
  if (!empty($errors)) {
    $error = implode("<br>", $errors);
  } else {
    $stmt = $pdo->prepare("SELECT user_id, username, password_hash, role FROM user_account WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {

      $_SESSION['user_id'] = $user['user_id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role'] = $user['role'];

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
}

include HEADER_FILE;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <title>Login</title>
</head>

<body>
  <main>
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
  </main>
</body>
<?php include FOOTER_FILE; ?>

</html>
