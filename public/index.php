<?php
include_once('../private/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Home</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php include_once HEADER_FILE;
  // Load featured vendors.
  $all_featured_vendors = Vendor::findAllWithFilters(['approved' => true]);
  $featured_vendors = array_slice($all_featured_vendors, 0, 4);
  ?>
  <main>
    <h2>WNC Farmers Markets Collective</h2>
    <p>Welcome to WNC Farmers Markets, your go-to resource for discovering fresh, local goods across Western North Carolina. Our platform connects communities with regional farmers, artisans, and small businesses, making it easy to find markets, vendors, and seasonal produce near you. Whether you're a shopper looking for farm-fresh ingredients or a vendor wanting to reach a wider audience, we're here to support and celebrate the vibrant local food scene.</p>

    <aside id="featured-vendors">
      <h2>Featured Vendors</h2>
      <div class="vendor-card-list">
        <?php foreach ($featured_vendors as $vendor): ?>
          <div class="vendor-card" data-vendor-id="<?= htmlspecialchars($vendor['vendor_id']); ?>">
            <img src="<?= htmlspecialchars($vendor['vendor_logo'] ?: 'img/default_vendor.jpg'); ?>" width="301" height="250" alt="<?= htmlspecialchars($vendor['vendor_name']); ?>">
            <div class="vendor-overlay">
              <h2><?= htmlspecialchars($vendor['vendor_name']); ?></h2>
              <p><?= htmlspecialchars($vendor['vendor_description']); ?></p>
              <a class="view-more" href="vendor-details.php?id=<?= htmlspecialchars($vendor['vendor_id']); ?>">View More</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </aside>
  </main>

  <section id="search">
    <section>
      <h2>Looking for something specific?</h2>
      <p>See who sells what you're looking for!</p>
      <!-- Search Form -->
      <form method="GET" action="index.php#results">
        <label for="search_term">Search for items:</label>
        <input type="text" name="search_term" id="search_term" placeholder="Search for items..."
          value="<?= isset($_GET['search_term']) ? htmlspecialchars($_GET['search_term']) : ''; ?>" />
        <button type="submit">Search</button>
      </form>

      <?php
      $search_term = isset($_GET['search_term']) ? trim(Utils::sanitize($_GET['search_term'])) : '';


      $vendors = [];
      $items = [];
      $suggested_term = null;

      if ($search_term) {
        $search_data = Item::searchItemsAndVendors($search_term); // Call the updated method
        $results = $search_data['results'];
        $suggested_term = $search_data['suggested_term'];

        if ($results) {
          foreach ($results as $row) {
            $items[$row['item_id']] = $row['item_name'];
            $vendors[$row['vendor_id']] = $row['vendor_name'];
          }
        }
      }
      ?>

      <!-- Spell Check Suggestion -->
      <?php if ($suggested_term && strtolower($suggested_term) !== strtolower($search_term)) : ?>
        <p>Did you mean:
          <a href="index.php?search_term=<?= urlencode($suggested_term) ?>#results">
            <strong><?= htmlspecialchars($suggested_term) ?></strong>
          </a>?
        </p>
      <?php endif; ?>

      <!-- Display Search Results -->
      <div id="results">
        <?php if ($search_term): ?>
          <h3>Search Results for: <?= htmlspecialchars($search_term) ?></h3>
          <?php if (!empty($items)) : ?>
            <ul>
              <?php foreach ($items as $item_id => $item_name) : ?>
                <li>
                  <strong><?= htmlspecialchars($item_name) ?></strong> - Vendors:
                  <ul>
                    <?php foreach ($vendors as $vendor_id => $vendor_name) : ?>
                      <li><a href="vendor-details.php?id=<?= htmlspecialchars($vendor_id) ?>">
                          <?= htmlspecialchars($vendor_name) ?>
                        </a>

                      </li>
                    <?php endforeach; ?>
                  </ul>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php else : ?>
            <p>No results found for "<?= htmlspecialchars($search_term) ?>"</p>
          <?php endif; ?>
        <?php endif; ?>
      </div>

    </section>
    <div>
      <p>Supporting Local, one market at a time.</p>
      <img src="img/smiley.svg" width="50" height="50" alt="A retro smiley face.">
    </div>

  </section>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
