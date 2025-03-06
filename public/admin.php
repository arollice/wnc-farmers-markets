<?php
include_once('../private/config.php');
session_start();
include_once HEADER_FILE;

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

$pdo = DatabaseObject::get_database();

// Process actions (approve, delete) submitted via POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $vendor_id = intval($_POST['vendor_id'] ?? 0);

  if ($vendor_id > 0 && $action === 'approve') {
    // Update vendor status to 'approved'
    $stmt = $pdo->prepare("UPDATE vendor SET status = 'approved' WHERE vendor_id = ?");
    if ($stmt->execute([$vendor_id])) {
      $_SESSION['success_message'] = "Vendor ID $vendor_id approved.";
    } else {
      $_SESSION['error_message'] = "Error approving vendor ID $vendor_id.";
    }
    header("Location: admin.php");
    exit;
  }

  if ($vendor_id > 0 && $action === 'delete') {
    // Delete vendor record (and related records if needed)
    $stmt = $pdo->prepare("DELETE FROM vendor WHERE vendor_id = ?");
    if ($stmt->execute([$vendor_id])) {
      $_SESSION['success_message'] = "Vendor ID $vendor_id deleted.";
    } else {
      $_SESSION['error_message'] = "Error deleting vendor ID $vendor_id.";
    }
    header("Location: admin.php");
    exit;
  }
}

// Fetch all vendors for display.
$stmt = $pdo->query("SELECT * FROM vendor");
$vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Admin Panel</title>
</head>

<body>
  <!-- Admin Panel Header -->
  <h2>Admin Dashboard</h2>
  <p>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</p>
  <a href="logout.php">Logout</a>
  <hr>

  <!-- Session Messages -->
  <?php
  if (isset($_SESSION['success_message'])) {
    echo "<div>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
    unset($_SESSION['success_message']);
  }
  if (isset($_SESSION['error_message'])) {
    echo "<div>" . htmlspecialchars($_SESSION['error_message']) . "</div>";
    unset($_SESSION['error_message']);
  }
  ?>

  <!-- Vendors Table for CRUD and Approval -->
  <h3>Vendor Management</h3>
  <?php if ($vendors): ?>
    <table border="1">
      <tr>
        <th>Vendor ID</th>
        <th>Name</th>
        <th>Website</th>
        <th>Description</th>
        <th>Status</th>
        <th>Approve</th>
        <th>Edit</th>
        <th>Delete</th>
      </tr>
      <?php foreach ($vendors as $vendor): ?>
        <tr>
          <td><?= htmlspecialchars($vendor['vendor_id']); ?></td>
          <td><?= htmlspecialchars($vendor['vendor_name']); ?></td>
          <td><?= htmlspecialchars($vendor['vendor_website']); ?></td>
          <td><?= htmlspecialchars($vendor['vendor_description']); ?></td>
          <td><?= htmlspecialchars($vendor['status']); ?></td>
          <td>
            <?php if ($vendor['status'] === 'pending'): ?>
              <form method="post">
                <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor['vendor_id']); ?>">
                <input type="hidden" name="action" value="approve">
                <button type="submit">Approve</button>
              </form>
            <?php else: ?>
              Approved
            <?php endif; ?>
          </td>
          <td>
            <a href="admin-edit-vendor.php?vendor_id=<?= htmlspecialchars($vendor['vendor_id']); ?>">Edit</a>

          </td>
          <td>
            <form method="post" onsubmit="return confirm('Are you sure you want to delete this vendor?');">
              <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor['vendor_id']); ?>">
              <input type="hidden" name="action" value="delete">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>No vendors found.</p>
  <?php endif; ?>

  <hr>
  <!-- Public Vendors Listing (Only Approved Vendors) -->
  <h3>Approved Vendors (Public Directory)</h3>
  <?php
  $stmt = $pdo->prepare("SELECT * FROM vendor WHERE status = 'approved'");
  $stmt->execute();
  $approved_vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
  if ($approved_vendors):
  ?>
    <ul>
      <?php foreach ($approved_vendors as $v): ?>
        <li><?= htmlspecialchars($v['vendor_name']); ?> - <?= htmlspecialchars($v['vendor_description']); ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else: ?>
    <p>No approved vendors at this time.</p>
  <?php endif; ?>
</body>

</html>
<?php include_once FOOTER_FILE; ?>
