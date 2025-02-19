<?php

// Function to fetch all records from a table, whitelist of allowed table names (prevents SQL injection risk)
$allowedTables = [
  'vendor',
  'vendor_item',
  'market',
  'vendor_market',
  'region',
  'item',
  'season',
  'policy_info',
  'users',
  'currency',
  'vendor_currency',
  'state'
];


// Function to fetch all records from a validated table
function fetchAll($table)
{
  global $pdo, $allowedTables;

  // Check if the requested table is in the whitelist
  if (!in_array($table, $allowedTables)) {
    die("Invalid table name: " . htmlspecialchars($table));
  }

  $stmt = $pdo->query("SELECT * FROM " . $table);
  return $stmt->fetchAll();
}


//Function to escape user input (for output safety)
function escape($input)
{
  return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

// Function to display a table (for debugging or admin views)
function displayTable($table)
{
  $rows = fetchAll($table);
  if (!$rows) {
    echo "<p>No records found.</p>";
    return;
  }

  echo "<table border='1'>";
  echo "<tr>";
  foreach (array_keys($rows[0]) as $column) {
    echo "<th>" . escape($column) . "</th>";
  }
  echo "</tr>";

  foreach ($rows as $row) {
    echo "<tr>";
    foreach ($row as $cell) {
      echo "<td>" . escape($cell) . "</td>";
    }
    echo "</tr>";
  }
  echo "</table>";
}
