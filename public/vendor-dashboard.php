<?php
include_once('../private/config.php');

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


<!--
Modify the Vendor Dashboard (vendor-dashboard.php)

Feature 1 "Manage Items" section:
a)-Display existing items they sell.
-Include a searchable input field for adding new items.
-Validate new items before inserting them.

b)Implement Backend Validation
If the item already exists, don't add a duplicate.
Otherwise, insert it and link it to the vendor.

Feature 2 Multi select for attending multiple markets:
<?php
/*
foreach ($markets as $market): ?>
    <option value="<?= htmlspecialchars($market['market_id']) ?>">
      <?= htmlspecialchars($market['market_name']) ?>
    </option>
<?php endforeach;
*/
?>
</select>
<?php
// And for the script tag, you might simply remove it or comment it out like this:
// echo '<script src="' . PUBLIC_PATH . '/js/vendor-form.js"></script>';
?>



-->
