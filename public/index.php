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

<h2>Seasonal Harvest Highlights</h2>
<p>Bringing the freshest and most flavorful produce to farmers markets highlights the unique bounty of each time of year. From vibrant spring greens and summer berries to autumn pumpkins and winter root vegetables, these offerings connect communities with the rhythm of local agriculture. They not only celebrate the diversity of regional crops but also provide an opportunity to explore new ingredients and support sustainable farming practices. Seasonal harvests are a key draw for farmers markets, offering visitors a chance to savor produce at its peak while deepening their appreciation for the cycle of the seasons.</p>
<img src="img/placeholder-img.png" width="150" height="auto" alt="Placeholder image.">
<p>Fruits</p>
<img src="img/placeholder-img.png" width="150" height="auto" alt="Placeholder image.">
<p>Vegetables</p>
<img src="img/placeholder-img.png" width="150" height="auto" alt="Placeholder image.">
<p>Meats & Poultry</p>
<img src="img/placeholder-img.png" width="150" height="auto" alt="Placeholder image.">
<p>Seasonal Plants & Greenery</p>

<section id="vendor-login">
  <p>Already a vendor? <a href="login.php">Login here</a>.</p>
</section>

<aside>
  <p>Supporting Local, one market at a time.</p>
  <img src="img/smiley.svg" width="50" height="auto" alt="A retro smiley face.">
</aside>


<?php
// Include the footer
include_once FOOTER_FILE;
?>
