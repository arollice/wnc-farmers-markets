<?php
include_once('../private/config.php');
include_once('../private/validation.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Admin Vendors</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  $pdo = DatabaseObject::get_database();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //CSRF
    $token = $_POST['csrf_token'] ?? null;
    if (! Utils::validateCsrf($token)) {
      Utils::setFlashMessage('error', 'Invalid CSRF token.');
      header('Location: admin-manage-vendors.php');
      exit;
    }

    $_POST     = Utils::sanitize($_POST);
    $action    = $_POST['action']     ?? '';
    $vendor_id = intval($_POST['vendor_id'] ?? 0);

    // Approve vendor
    if ($vendor_id > 0 && $action === 'approve') {
      $vendor = Vendor::find_by_id($vendor_id);
      // Debug mode—dump how many rows match:
      $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_account WHERE vendor_id = ?");
      $stmt->execute([$vendor_id]);
      $count = $stmt->fetchColumn();
      Utils::setFlashMessage('info', "Found $count user_account rows for vendor_id={$vendor_id}");

      if ($vendor) {
        $vendor->status = 'approved';
        if ($vendor->save()) {
          Utils::setFlashMessage('success', "Vendor '{$vendor->vendor_name}' approved.");
        } else {
          Utils::setFlashMessage('error', "Error approving vendor '{$vendor->vendor_name}'.");
        }
      } else {
        Utils::setFlashMessage('error', "Vendor not found.");
      }
      header('Location: admin-manage-vendors.php');
      exit;
    }

    // Delete vendor
    if ($vendor_id > 0 && $action === 'delete') {
      $pdo->beginTransaction();
      try {
        // Clean up the many-to-many links for **this** vendor only
        $stmt = $pdo->prepare("DELETE FROM vendor_item WHERE vendor_id = ?");
        $stmt->execute([$vendor_id]);

        $stmt = $pdo->prepare("DELETE FROM vendor_market WHERE vendor_id = ?");
        $stmt->execute([$vendor_id]);

        // If you have a payments‐link table (e.g. vendor_currency or vendor_payment):
        $stmt = $pdo->prepare("DELETE FROM vendor_currency WHERE vendor_id = ?");
        $stmt->execute([$vendor_id]);

        // Delete the user_account row that points to this vendor
        $stmt = $pdo->prepare("DELETE FROM user_account WHERE vendor_id = ?");
        $stmt->execute([$vendor_id]);

        // Last delete the vendor record itself
        $vendor = Vendor::find_by_id($vendor_id);
        if (! $vendor || ! $vendor->delete()) {
          throw new Exception("Could not delete vendor record.");
        }

        $pdo->commit();
        Utils::setFlashMessage('success', "Vendor and all related data have been deleted.");
      } catch (Exception $e) {
        $pdo->rollBack();
        Utils::setFlashMessage('error', "Deletion failed: " . $e->getMessage());
      }

      header('Location: admin-manage-vendors.php');
      exit;
    }
  }

  include_once HEADER_FILE;
  ?>

  <main>
    <header class="dashboard-header">
      <h2>Manage Vendors</h2>
      <p><a href="admin.php">&larr; Back to Dashboard</a></p>
    </header>
    <?php Utils::displayFlashMessages(); ?>

    <section>
      <h3>Vendor Accounts</h3>
      <?php
      $stmt = $pdo->query("SELECT * FROM vendor");
      $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if ($vendors):
      ?>
        <table id="vendor-accounts">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Description</th>
              <th>Status</th>
              <th>View</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($vendors as $vendor): ?>
              <tr>
                <td><?= htmlspecialchars($vendor['vendor_id']); ?></td>
                <td><?= htmlspecialchars($vendor['vendor_name']); ?></td>
                <td><?= htmlspecialchars($vendor['vendor_description']); ?></td>
                <td class="<?= $vendor['status'] === 'approved' ? 'approved-cell' : '' ?>">
                  <?php if ($vendor['status'] === 'pending'): ?>
                    <form method="post">
                      <?= Utils::csrfInputTag() ?>
                      <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor['vendor_id'], ENT_QUOTES) ?>">
                      <input type="hidden" name="action" value="approve">
                      <button type="submit">Approve</button>
                    </form>
                  <?php else: ?>
                    <button disabled class="disabled-btn">Approved</button>
                  <?php endif; ?>
                </td>
                <td><a href="vendor-details.php?id=<?= htmlspecialchars($vendor['vendor_id'], ENT_QUOTES) ?>">View</a></td>
                <td><a href="admin-edit-vendor.php?vendor_id=<?= htmlspecialchars($vendor['vendor_id'], ENT_QUOTES) ?>">Edit</a></td>
                <td>
                  <form method="post" onsubmit="return confirm('Delete this vendor?');">
                    <?= Utils::csrfInputTag() ?>
                    <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor['vendor_id'], ENT_QUOTES) ?>">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php else: ?>
        <p>No vendors found.</p>
      <?php endif; ?>
    </section>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
