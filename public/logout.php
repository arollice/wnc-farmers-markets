<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include_once('../private/config.php');
include HEADER_FILE;

// Capture the username for our logout message.
$username = $_SESSION['username'] ?? 'User';

// Unset all session variables.
$_SESSION = array();

// Delete the session cookie if sessions use cookies.
if (ini_get("session.use_cookies")) {
  $params = session_get_cookie_params();
  setcookie(
    session_name(),
    '',
    time() - 42000,
    $params["path"],
    $params["domain"],
    $params["secure"],
    $params["httponly"]
  );
}

session_destroy();

// Start a new session for the flash message.
session_start();
$_SESSION['success_message'] = "$username, is now logged out";
?>
<div class="logout-message">
  <p><?= $_SESSION['success_message'] ?></p>
  <p><a href="index.php">Return to Home</a></p>
</div>
<?php include FOOTER_FILE; ?>
