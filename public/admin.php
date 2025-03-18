<?php
include_once('../private/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

$pdo = DatabaseObject::get_database();

// Process actions (approve, delete, delete_admin) submitted via POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $vendor_id = intval($_POST['vendor_id'] ?? 0);

  if ($vendor_id > 0 && $action === 'approve') {
    // Retrieve the Vendor object.
    $vendor = Vendor::find_by_id($vendor_id);
    if ($vendor) {
      // Set the status to approved.
      $vendor->status = 'approved';
      if ($vendor->save()) {
        $_SESSION['success_message'] = "Vendor ID $vendor_id approved.";
      } else {
        $_SESSION['error_message'] = "Error approving vendor ID $vendor_id.";
      }
    } else {
      $_SESSION['error_message'] = "Vendor not found.";
    }
    header("Location: admin.php");
    exit;
  }

  if ($vendor_id > 0 && $action === 'delete') {
    // Retrieve the vendor object.
    $vendor = Vendor::find_by_id($vendor_id);
    if ($vendor && $vendor->delete()) {
      $_SESSION['success_message'] = "Vendor ID $vendor_id deleted.";
    } else {
      $_SESSION['error_message'] = "Error deleting vendor ID $vendor_id.";
    }
    header("Location: admin.php");
    exit;
  }

  // Process delete admin action.
  $admin_id = intval($_POST['admin_id'] ?? 0);
  if ($admin_id > 0 && $action === 'delete_admin') {
    // Prevent an admin from deleting their own account.
    if ($admin_id == $_SESSION['user_id']) {
      $_SESSION['error_message'] = "You cannot delete your own admin account.";
    } else {
      $admin = UserAccount::find_by_id($admin_id);
      if ($admin && $admin->delete()) {
        $_SESSION['success_message'] = "Admin ID $admin_id deleted.";
      } else {
        $_SESSION['error_message'] = "Error deleting admin ID $admin_id.";
      }
    }
    header("Location: admin.php");
    exit;
  }
}

// Fetch all vendors for display.
$stmt = $pdo->query("SELECT * FROM vendor");
$vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

include_once HEADER_FILE;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Admin Panel</title>
  <!-- No inline styles for buttons; use your external CSS file for styling .disabled-btn etc. -->
</head>

<body>
  <main>
    <header class="dashboard-header">
      <h2>Admin Dashboard</h2>
      <p>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</p>
      <a href="logout.php">Logout</a>
    </header>
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

    <!-- Admin Accounts Management Section -->
    <section id="admin-account-management">
      <h3>Admin Accounts Management</h3>
      <p><a href="create-admin.php" class="add-admin-link">+ Add Admin</a></p>
      <?php
      // Fetch all admin accounts from the user_account table.
      $stmt = $pdo->prepare("SELECT * FROM user_account WHERE role = 'admin'");
      $stmt->execute();
      $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if ($admins):
      ?>
        <table>
          <tr>
            <th>Admin ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
          </tr>
          <?php foreach ($admins as $admin): ?>
            <tr>
              <td class="table-admin-id"><?= htmlspecialchars($admin['user_id']); ?></td>
              <td><?= htmlspecialchars($admin['username']); ?></td>
              <td><?= htmlspecialchars($admin['email']); ?></td>
              <td>
                <?php if ($admin['user_id'] != $_SESSION['user_id']): ?>
                  <form method="post" onsubmit="return confirm('Are you sure you want to delete this admin?');">
                    <input type="hidden" name="admin_id" value="<?= htmlspecialchars($admin['user_id']); ?>">
                    <input type="hidden" name="action" value="delete_admin">
                    <button type="submit">Delete Admin</button>
                  </form>
                <?php else: ?>
                  <!-- Disabled button for the current admin -->
                  <button type="button" class="disabled-btn" disabled>Current Admin</button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php else: ?>
        <p>No admin accounts found.</p>
      <?php endif; ?>
    </section>

    <!-- Vendors Table for CRUD and Approval -->
    <h3>Vendor Management</h3>
    <?php if ($vendors): ?>
      <table>
        <tr>
          <th>Vendor ID</th>
          <th>Name</th>
          <th>Website</th>
          <th>Description</th>
          <th>Status</th>
          <th>View</th>
          <th>Edit</th>
          <th>Delete</th>
        </tr>
        <?php foreach ($vendors as $vendor): ?>
          <tr>
            <td class="table-vendor-id"><?= htmlspecialchars($vendor['vendor_id']); ?></td>
            <td><?= htmlspecialchars($vendor['vendor_name']); ?></td>
            <td><?= htmlspecialchars($vendor['vendor_website']); ?></td>
            <td><?= htmlspecialchars($vendor['vendor_description']); ?></td>
            <td class="<?= $vendor['status'] === 'approved' ? 'approved-cell' : '' ?>">
              <?php if ($vendor['status'] === 'pending'): ?>
                <form method="post">
                  <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor['vendor_id']); ?>">
                  <input type="hidden" name="action" value="approve">
                  <button type="submit">Approve</button>
                </form>
              <?php else: ?>
                <button type="button" class="disabled-btn" disabled>Approved</button>
              <?php endif; ?>
            </td>
            <td>
              <a href="vendor-details.php?id=<?= htmlspecialchars($vendor['vendor_id']); ?>">View</a>
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
  </main>
</body>
<?php include_once FOOTER_FILE; ?>

</html>
