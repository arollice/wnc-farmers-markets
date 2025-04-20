<?php
include_once('../private/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Logout</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User';

  // Unset all session variables.
  $_SESSION = array();

  // Delete the session cookie if sessions use cookies.
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
  }

  session_destroy();

  // Start a new session for the flash message.
  session_start();
  $_SESSION['success_message'] = htmlspecialchars("$username, you are now logged out.");

  include HEADER_FILE;
  ?>

  <main class="logout-message">
    <p><?= htmlspecialchars($_SESSION['success_message']) ?></p>

    <p><a href="index.php">Return to Home</a></p>
  </main>

  <?php include FOOTER_FILE; ?>
</body>

</html>
