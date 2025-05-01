<?php
include_once __DIR__ . '/../private/config.php';
include_once __DIR__ . '/../private/validation.php';

$error    = '';
$username = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $_POST    = Utils::sanitize($_POST);
  $captcha  = trim($_POST['captcha_code'] ?? '');
  $username = trim($_POST['username']     ?? '');
  $password = trim($_POST['password']     ?? '');

  // CSRF check
  if (! Utils::validateCsrf($_POST['csrf_token'] ?? null)) {
    $error = 'Invalid form submission; please try again.';
  }

  // CAPTCHA check
  if (empty($error) && ! Utils::checkCaptcha($captcha)) {
    $error = 'Incorrect CAPTCHA; please try again.';
  }

  // If above passed, validate credentials
  if (empty($error)) {
    $errs = validateLoginFields($username, $password);
    if (! empty($errs)) {
      $error = implode("<br>", $errs);
    } else {
      // 4) Lookup user & verify
      $stmt = $pdo->prepare(
        "SELECT user_id, username, password_hash, role
                   FROM user_account
                  WHERE username = ?
                  LIMIT 1"
      );
      $stmt->execute([$username]);
      $user = $stmt->fetch();

      if ($user && password_verify($password, $user['password_hash'])) {
        // Success → set session + redirect
        $_SESSION['user_id']  = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        switch ($user['role']) {
          case 'admin':
            $dest = 'admin.php';
            break;
          case 'vendor':
            $dest = 'vendor-dashboard.php';
            break;
          default:
            $dest = 'index.php';
        }
        header("Location: $dest");
        exit;
      }

      $error = 'Invalid username or password';
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Login</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php include HEADER_FILE; ?>
  <main>
    <h2>Login</h2>

    <?php if ($error): ?>
      <p class="register-error"><?= htmlspecialchars($error) ?></p>
    <?php endif ?>

    <form method="post">
      <!-- CSRF token -->
      <input
        type="hidden"
        name="csrf_token"
        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES) ?>">

      <div class="form__group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          autocomplete="username"
          value="<?= htmlspecialchars($username) ?>"
          required>
      </div>

      <div class="form__group">
        <label for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          autocomplete="current-password"
          required>
      </div>

      <!-- CAPTCHA with refresh -->
      <div class="form__captcha">
        <img
          src="captcha.php?<?= time() ?>"
          alt="CAPTCHA code"
          class="form__captcha-img">
        <button
          type="button"
          class="form__captcha-refresh"
          onclick="document.querySelector('.form__captcha-img').src='captcha.php?'+Date.now()">
          &orarr; Refresh
        </button>
      </div>

      <div class="form__group">
        <label for="captcha_code">Enter the code shown</label>
        <input
          type="text"
          id="captcha_code"
          name="captcha_code"
          required>
      </div>

      <button type="submit" class="btn btn--primary">Log In</button>
    </form>
  </main>

  <?php include FOOTER_FILE; ?>
</body>

</html>
