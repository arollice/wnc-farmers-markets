<?php
if (!defined('PUBLIC_PATH')) {
  include_once __DIR__ . '/../private/config.php';
}

// Determine the current URI (used for setting active navigation states)
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<header id="reusable-header">
  <a href="<?= PUBLIC_PATH ?>/index.php">
    <img src="<?= PUBLIC_PATH ?>/img/wnc-logo-color.webp" alt="WNC Farmers Markets Logo" width="200" height="150">
  </a>
  <p><em>Your central resource for farmers markets in Western North Carolina</em></p>

  <input type="checkbox" id="menu-toggle-checkbox" hidden>
  <!-- Label acts as the hamburger icon -->
  <label for="menu-toggle-checkbox" id="menu-toggle-label" aria-label="Toggle navigation">&#9776;</label>

  <nav id="nav-menu">
    <ul>
      <li>
        <a href="<?= PUBLIC_PATH ?>/index.php"
          class="<?= ($currentUri == PUBLIC_PATH . '/index.php' || $currentUri == PUBLIC_PATH . '/') ? 'active' : '' ?>">
          Home
        </a>
      </li>
      <li>
        <a href="<?= PUBLIC_PATH ?>/regions.php"
          class="<?= ($currentUri == PUBLIC_PATH . '/regions.php') ? 'active' : '' ?>">
          Regions
        </a>
      </li>
      <li>
        <a href="<?= PUBLIC_PATH ?>/markets.php"
          class="<?= ($currentUri == PUBLIC_PATH . '/markets.php') ? 'active' : '' ?>">
          Markets
        </a>
      </li>
      <li>
        <a href="<?= PUBLIC_PATH ?>/vendors.php"
          class="<?= ($currentUri == PUBLIC_PATH . '/vendors.php') ? 'active' : '' ?>">
          Vendors
        </a>
      </li>
      <li>
        <a href="<?= PUBLIC_PATH ?>/about.php"
          class="<?= ($currentUri == PUBLIC_PATH . '/about.php') ? 'active' : '' ?>">
          About
        </a>
      </li>
    </ul>
  </nav>
</header>
