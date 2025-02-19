<?php
require_once __DIR__ . '/../private/rollice-ashlee-db-connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'] ?? '';
  $password = $_POST['password'] ?? '';
  $email = $_POST['email'] ?? '';
  $role = $_POST['role'] ?? 'user'; // Default role is 'user'

  // Hash password
  $hashedPassword = hash('sha256', $password);

  // Insert into database
  $stmt = $pdo->prepare("INSERT INTO user_account (username, password_hash, email, role, created_at, is_active) VALUES (?, ?, ?, ?, NOW(), 1)");
  try {
    $stmt->execute([$username, $hashedPassword, $email, $role]);
    $success = "Registration successful! You can now log in.";
  } catch (PDOException $e) {
    $error = "Error: Could not register user. Username or email may already be taken.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Register</title>
</head>

<body>
  <h2>Register</h2>
  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
  <?php if (!empty($success)) echo "<p style='color:green;'>$success</p>"; ?>
  <form method="POST">
    <label>Username: <input type="text" name="username" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Password: <input type="password" name="password" required></label><br>
    <label>Role:
      <select name="role">
        <option value="user">User</option>
        <option value="vendor">Vendor</option>
      </select>
    </label><br>
    <button type="submit">Register</button>
  </form>
</body>

</html>
