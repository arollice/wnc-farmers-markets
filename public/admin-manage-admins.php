<?php
include_once('../private/config.php');
include_once('../private/validation.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Admin Manage Admins</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
  }

  $pdo = DatabaseObject::get_database();

  // Edit mode for admin accounts 
  $edit_admin_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF & sanitize…
    $token = $_POST['csrf_token'] ?? null;
    if (! Utils::validateCsrf($token)) {
      Utils::setFlashMessage('error', 'Invalid CSRF token.');
      header('Location: admin-manage-admins.php');
      exit;
    }
    $_POST = Utils::sanitize($_POST);

    $action   = $_POST['action']   ?? '';
    $admin_id = intval($_POST['admin_id'] ?? 0);

    if ($admin_id > 0) {

      $stmt = $pdo->prepare("SELECT username FROM user_account WHERE user_id = ?");
      $stmt->execute([$admin_id]);
      $name = $stmt->fetchColumn() ?: "ID {$admin_id}";

      if ($action === 'edit_admin') {
        UserAccount::processAdminAction($_POST);
        Utils::setFlashMessage('success', "Admin {$name} updated.");
        header('Location: admin-manage-admins.php');
        exit;
      }

      if ($action === 'delete_admin') {
        // Delete inside a transaction
        $pdo->beginTransaction();
        try {

          // Only delete admin‐role accounts:
          $stmt = $pdo->prepare("
            DELETE FROM user_account
             WHERE user_id = ?
               AND role    = 'admin'
          ");
          $stmt->execute([$admin_id]);
          if ($stmt->rowCount() === 0) {
            throw new Exception("No admin found or cannot delete non-admin.");
          }
          $pdo->commit();
          Utils::setFlashMessage('success', "Admin {$name} deleted.");
        } catch (Exception $e) {
          $pdo->rollBack();
          Utils::setFlashMessage('error', "Deletion failed: " . $e->getMessage());
        }
        header('Location: admin-manage-admins.php');
        exit;
      }
    }
  }


  include_once HEADER_FILE;
  ?>

  <main>
    <header class="dashboard-header">
      <h2>Manage Admins</h2>
      <p><a href="admin.php">&larr; Back to Dashboard</a></p>
    </header>
    <?php Utils::displayFlashMessages(); ?>

    <section>
      <h3>Admin Accounts</h3>
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
              <th>ID</th>
              <th>Username</th>
              <th>Email</th>
              <th>Edit</th>
              <th>Delete</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($admins as $admin): ?>
              <?php if ($admin['user_id'] === $edit_admin_id): ?>
                <?php
                // unique form id per row
                $fid = "edit-admin-form-{$admin['user_id']}";
                ?>
                <!-- Hidden form carrying CSRF token, admin_id & action -->
                <form
                  id="<?= $fid ?>"
                  method="post"
                  style="display:none;">
                  <?= Utils::csrfInputTag() ?>
                  <input
                    type="hidden"
                    name="admin_id"
                    value="<?= htmlspecialchars($admin['user_id'], ENT_QUOTES) ?>">
                  <input
                    type="hidden"
                    name="action"
                    value="edit_admin">
                </form>

                <tr>
                  <td><?= htmlspecialchars($admin['user_id'], ENT_QUOTES) ?></td>
                  <td>
                    <input
                      form="<?= $fid ?>"
                      type="text"
                      name="username"
                      value="<?= htmlspecialchars($admin['username'], ENT_QUOTES) ?>">
                  </td>
                  <td>
                    <input
                      form="<?= $fid ?>"
                      type="email"
                      name="email"
                      value="<?= htmlspecialchars($admin['email'], ENT_QUOTES) ?>">
                  </td>
                  <td>
                    <button
                      form="<?= $fid ?>"
                      type="submit">
                      Save
                    </button>
                  </td>
                  <td>
                    <a href="admin-manage-admins.php">Cancel</a>
                  </td>
                </tr>

              <?php else: ?>
                <tr>
                  <td><?= htmlspecialchars($admin['user_id'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars($admin['username'], ENT_QUOTES) ?></td>
                  <td><?= htmlspecialchars($admin['email'], ENT_QUOTES) ?></td>
                  <td>
                    <?php if ($admin['user_id'] == $_SESSION['user_id']): ?>
                      <del>Edit</del>
                    <?php else: ?>
                      <a href="admin-manage-admins.php?edit=<?= htmlspecialchars($admin['user_id'], ENT_QUOTES) ?>">Edit</a>
                    <?php endif; ?>
                  </td>
                  <td>
                    <?php if ($admin['user_id'] == $_SESSION['user_id']): ?>
                      <button type="button" class="disabled-btn" disabled>Current Admin</button>
                    <?php else: ?>
                      <form
                        method="post"
                        onsubmit="return confirm('Are you sure you want to delete this admin?');"
                        style="display:inline;">
                        <?= Utils::csrfInputTag() ?>
                        <input
                          type="hidden"
                          name="admin_id"
                          value="<?= htmlspecialchars($admin['user_id'], ENT_QUOTES) ?>">
                        <input
                          type="hidden"
                          name="action"
                          value="delete_admin">
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
  </main>
  <?php include_once FOOTER_FILE; ?>
</body>

</html>
