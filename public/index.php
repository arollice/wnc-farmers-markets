<?php
include_once('../private/config.php');
?>

<h1>WNC Farmers Market - Coming Soon!</h1>
<p>We're working hard to bring you the best farmers market experience.</p>

<?php // Include the header
include_once HEADER_FILE;
?>

<h2>Market Locations</h2>
<?php displayTable('market'); ?>

<?php
// Include the footer
include_once FOOTER_FILE;
?>
