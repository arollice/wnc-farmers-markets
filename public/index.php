<?php
require_once __DIR__ . '/../private/db-credentials.php';
require_once __DIR__ . '/../private/db-functions.php';
require_once __DIR__ . '/../private/rollice-ashlee-db-connection.php';
?>

<h1>WNC Farmers Market - Coming Soon!</h1>
<p>We're working hard to bring you the best farmers market experience.</p>

<?php include_once __DIR__ . '/shared/header.php'; // Updated path
?>

<h2>Market Locations</h2>
<?php displayTable('market'); ?>

<?php include_once __DIR__ . '/shared/footer.php'; // Updated path 
?>
