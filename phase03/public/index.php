<?php
require_once __DIR__ . '/../private/db-credentials.php';
require_once __DIR__ . '/../private/db-functions.php';
require_once __DIR__ . '/../private/rollice-ashlee-db-connection.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Coming Soon - WNC Farmers Market</title>
</head>

<body>

  <h1>WNC Farmers Market - Coming Soon!</h1>
  <p>We're working hard to bring you the best farmers market experience.</p>

  <h2> Market Locations</h2>
  <?php displayTable('market'); ?>

  <footer>
    <p><strong>Disclaimer:</strong> This website is developed as part of a <strong>Capstone Project</strong> and is for educational purposes only. All data displayed is <strong>sample data</strong> and should not be considered factual or relied upon for real-world decision-making.</p>
  </footer>

</body>

</html>
