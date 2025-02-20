<?php
include_once('../private/config.php');
?>

<h1>WNC Farmers Market - Coming Soon!</h1>
<p>We're working hard to bring you the best farmers market experience.</p>

<?php // Include the header
include_once HEADER_FILE;
?>
<div class="homepage-buttons">
  <a href="<?= PUBLIC_PATH ?>/regions.php" class="btn">Find a Market</a>
  <a href="<?= PUBLIC_PATH ?>/vendor-register.php" class="btn">Become a Vendor</a>
</div>

<h2>Market Locations</h2>
<?php displayTable('market'); ?>

<?php
// Include the footer
include_once FOOTER_FILE;
?>


<!-- 
 //Add seasonal harvest highlights
 //Add vendor login link in bottom corner (already a member? login)
 //Add supporting local, one vendor at a time and smiley
-->
