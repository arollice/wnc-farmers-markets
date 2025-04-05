<?php
include_once __DIR__ . '/../private/config.php';
include_once __DIR__ . '/../private/validation.php';

$error = '';

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
      $_SESSION['user_id']  = $user['user_id'];
      $_SESSION['username'] = $user['username'];
      $_SESSION['role']     = $user['role'];

      switch ($user['role']) {
        case 'admin':
          header('Location: admin.php');
          break;
        case 'vendor':
          header('Location: vendor-dashboard.php');
          break;
        default:
          header('Location: index.php');
          break;
      }
      exit;
    } else {
      $error = "Invalid username or password";
    }
  }
}

include HEADER_FILE;
?>

<main>
  <h2>Login</h2>
  <?php
  if (!empty($error)) {
    echo "<p style='color:red;'>" . htmlspecialchars($error) . "</p>";
  }
  ?>

  <form method="POST">
    <label for="username">Username:</label>
    <input type="text" id="username" name="username" required>
    <br>
    <label for="password">Password:</label>
    <input type="password" id="password" name="password" required>
    <br>
    <button type="submit">Login</button>
  </form>
</main>

<?php include FOOTER_FILE; ?>
