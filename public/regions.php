<?php
require_once __DIR__ . '/../private/db-credentials.php';
require_once __DIR__ . '/../private/db-functions.php';
require_once __DIR__ . '/../private/rollice-ashlee-db-connection.php';

// Include the header
include_once __DIR__ . '/shared/header.php';
?>

<h1>Find a Market by Region</h1>
<p>Select a region below to explore available markets.</p>

<!-- Placeholder for listing regions dynamically -->
<ul>
  <?php
  // Fetch regions from the database
  $query = "SELECT id, name FROM region ORDER BY name ASC";
  $stmt = $pdo->prepare($query);
  $stmt->execute();
  $regions = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($regions as $region) {
    echo "<li><a href='markets.php?region=" . htmlspecialchars($region['id']) . "'>" . htmlspecialchars($region['name']) . "</a></li>";
  }
  ?>
</ul>

<?php
include_once __DIR__ . '/shared/footer.php';
?>
