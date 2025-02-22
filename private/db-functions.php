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

function fetchMarketDetails($market_id)
{
  global $pdo;

  try {
    $sql = "SELECT 
                    m.market_name, 
                    m.city, 
                    s.state_name, 
                    m.zip_code, 
                    m.parking_info,
                    GROUP_CONCAT(DISTINCT CONCAT(ms.market_day, ' (Season: ', se.season_name, ')') ORDER BY FIELD(ms.market_day, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') SEPARATOR ', ') AS market_schedule,
                    GROUP_CONCAT(DISTINCT ms.market_day ORDER BY FIELD(ms.market_day, 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') SEPARATOR ', ') AS market_days,
                    GROUP_CONCAT(DISTINCT se.season_name ORDER BY se.season_name ASC SEPARATOR ', ') AS market_season,
                    MAX(ms.last_day_of_season) AS last_market_date
                FROM market m
                LEFT JOIN state s ON m.state_id = s.state_id
                LEFT JOIN market_schedule ms ON m.market_id = ms.market_id
                LEFT JOIN season se ON ms.season_id = se.season_id
                WHERE m.market_id = :market_id
                GROUP BY m.market_id";

    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':market_id', $market_id, PDO::PARAM_INT);
    $stmt->execute();

    $market = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$market) {
      throw new Exception("No market found for ID: " . $market_id);
    }

    return $market;
  } catch (Exception $e) {
    die("SQL Error: " . $e->getMessage());
  }
}



function fetchMarketPolicies()
{
  global $pdo;

  try {
    $sql = "SELECT policy_name, policy_description 
                FROM policy_info
                WHERE policy_name IN ('Pet-Friendly Market', 'SNAP/EBT Accepted')"; // Only include these two policies

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } catch (Exception $e) {
    die("SQL Error: " . $e->getMessage());
  }
}
