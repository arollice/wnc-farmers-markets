<?php
include_once('../private/config.php');
include_once('../private/validation.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Admin Home</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  // Check if admin is logged in.
  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
  }

  $pdo = DatabaseObject::get_database();

  // Process POST requests
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_POST = Utils::sanitize($_POST);
    $action = $_POST['action'] ?? '';
    $vendor_id = intval($_POST['vendor_id'] ?? 0);
    $admin_id = intval($_POST['admin_id'] ?? 0);

    // Process admin-related actions
    if ($admin_id > 0 && in_array($action, ['edit_admin', 'delete_admin'])) {
      UserAccount::processAdminAction($_POST);
    }

    // Approve Vendor
    if ($vendor_id > 0 && $action === 'approve') {
      $vendor = Vendor::find_by_id($vendor_id);
      if ($vendor) {
        $vendor->status = 'approved';
        if ($vendor->save()) {
          // Using vendor_name instead of the vendor_id.
          Utils::setFlashMessage('success', "Vendor '{$vendor->vendor_name}' approved.");
        } else {
          Utils::setFlashMessage('error', "Error approving vendor '{$vendor->vendor_name}'.");
        }
      } else {
        Utils::setFlashMessage('error', "Vendor not found.");
      }
      header("Location: admin.php");
      exit;
    }

    // Delete Vendor
    if ($vendor_id > 0 && $action === 'delete') {
      $vendor = Vendor::find_by_id($vendor_id);
      if ($vendor && $vendor->delete()) {
        Utils::setFlashMessage('success', "Vendor '{$vendor->vendor_name}' deleted.");
      } else {
        Utils::setFlashMessage('error', "Error deleting vendor.");
      }
      header("Location: admin.php");
      exit;
    }
  }

  // Set the edit mode for admin accounts from the GET parameter.
  $edit_admin_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

  // Fetch all vendors for display.
  $stmt = $pdo->query("SELECT * FROM vendor");
  $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

  include_once HEADER_FILE;
  ?>

  <main>
    <header class="dashboard-header">
      <h2>Admin Dashboard</h2>
      <p>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'Admin'); ?>!</p>
      <a href="logout.php">Logout</a>
    </header>
    <hr>

    <?php Utils::displayFlashMessages(); ?>
    <section>
      <h3>Admin Accounts Management</h3>
      <p><a href="create-admin.php" class="add-admin-link">+ Add Admin</a></p>
      <?php
      $stmt = $pdo->prepare("SELECT * FROM user_account WHERE role = 'admin'");
      $stmt->execute();
      $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if ($admins):
      ?>
        <table id="admin-accounts">
          <thead>
            <tr>
              <th>Admin ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($admins as $admin): ?>
              <?php if ($admin['user_id'] === $edit_admin_id): ?>
                <form method="post">
                  <tr>
                    <td>
                      <?= htmlspecialchars($admin['user_id']); ?>
                      <input type="hidden" name="admin_id" value="<?= htmlspecialchars($admin['user_id']); ?>">
                    </td>
                    <td>
                      <input type="text" name="username" value="<?= htmlspecialchars($admin['username']); ?>">
                    </td>
                    <td>
                      <input type="email" name="email" value="<?= htmlspecialchars($admin['email']); ?>">
                    </td>
                    <td>
                      <input type="hidden" name="action" value="edit_admin">
                      <button type="submit">Save</button>
                    </td>
                    <td>
                      <a href="admin.php">Cancel</a>
                    </td>
                  </tr>
                </form>
              <?php else: ?>
                <tr>
                  <td><?= htmlspecialchars($admin['user_id']); ?></td>
                  <td><?= htmlspecialchars($admin['username']); ?></td>
                  <td><?= htmlspecialchars($admin['email']); ?></td>
                  <td>
                    <?php if ($admin['user_id'] == $_SESSION['user_id']): ?>
                      <del>Edit</del>
                    <?php else: ?>
                      <a href="admin.php?edit=<?= htmlspecialchars($admin['user_id']); ?>">Edit</a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($admin['user_id'] == $_SESSION['user_id']): ?>
                      <button type="button" class="disabled-btn" disabled>Current Admin</button>
                    <?php else: ?>
                      <form method="post" onsubmit="return confirm('Are you sure you want to delete this admin?');" style="display:inline;">
                        <input type="hidden" name="admin_id" value="<?= htmlspecialchars($admin['user_id']); ?>">
                        <input type="hidden" name="action" value="delete_admin">
                        <button type="submit">Delete</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endif; ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No admin accounts found.</p>
      <?php endif; ?>
    </section>

    <!-- Vendors Table for CRUD and Approval -->
    <section>
      <h3>Vendor Management</h3>
      <?php if ($vendors): ?>
        <table id="vendor-accounts">
          <tr>
            <th>Vendor ID</th>
            <th>Name</th>
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
    </section>
    <hr>

    <!-- Public Vendors Listing (Only Approved Vendors) -->
    <h3>Approved Vendors (Public Directory)</h3>
    <?php
    $stmt = $pdo->prepare("SELECT * FROM vendor WHERE status = 'approved'");
    $stmt->execute();
    $approved_vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($approved_vendors):
    ?>
      <ul id="approved-vendors">
        <?php foreach ($approved_vendors as $v): ?>
          <li><?= htmlspecialchars($v['vendor_name']); ?> - <?= htmlspecialchars($v['vendor_description']); ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No approved vendors at this time.</p>
    <?php endif; ?>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
