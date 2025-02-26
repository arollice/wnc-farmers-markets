<?php
include_once('../private/config_api.php'); // Ensure this contains SAPLING_API_KEY

class Item extends DatabaseObject
{
  static protected $table_name = "item";
  static protected $db_columns = ['item_id', 'item_name'];
  static protected $primary_key = 'item_id';

  public $item_id;
  public $item_name;

  public static function findItemsByVendor($vendor_id)
  {
    if (!isset(self::$database)) {
      die("Database connection is not established.");
    }

    $sql = "SELECT i.item_id, i.item_name 
            FROM item i
            JOIN vendor_item vi ON i.item_id = vi.item_id
            WHERE vi.vendor_id = ?";

    $stmt = self::$database->prepare($sql);
    $stmt->execute([$vendor_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function searchItemsAndVendors($search_term)
  {
    $pdo = self::$database;

    // Call Sapling Spell Check API to correct search term
    $corrected_term = self::getSpellCheckedTerm($search_term);
    $final_search_term = $corrected_term ?: $search_term; // Use corrected term if available

    // Search vendors based on the corrected or original search term
    $query = "SELECT i.item_id, i.item_name, v.vendor_id, v.vendor_name
              FROM item i
              JOIN vendor_item vi ON i.item_id = vi.item_id
              JOIN vendor v ON vi.vendor_id = v.vendor_id
              WHERE i.item_name LIKE :search_term
              ORDER BY i.item_name ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute([':search_term' => '%' . $final_search_term . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return [
      'results' => $results,
      'suggested_term' => ($corrected_term !== null && $corrected_term !== $search_term) ? $corrected_term : null
    ];
  }

  private static function getSpellCheckedTerm($text)
  {
    // Custom Dictionary of Common Misspellings
    $manual_corrections = [
      'meet' => 'meat',
      'met' => 'meat',
      'appel' => 'apple',
      'lettcue' => 'lettuce',
      'strwabery' => 'strawberry',
      'bluberry' => 'blueberry',
      'honeyy' => 'honey',
      'poutlry' => 'poultry',
      'pultry' => 'poultry',
      'poltry' => 'poultry',
      'diary' => 'dairy',
      'dairey' => 'dairy',
      'darry' => 'dairy',
      'sirup' => 'syrup'
    ];

    $lower_text = strtolower($text);
    if (array_key_exists($lower_text, $manual_corrections)) {
      return $manual_corrections[$lower_text];
    }

    $api_key = SAPLING_API_KEY;
    if (!$api_key) {
      error_log("Sapling API key is missing.");
      return null;
    }

    $endpoint = 'https://api.sapling.ai/api/v1/edits';

    $data = [
      'key' => $api_key,
      'text' => $text,
      'session_id' => uniqid()
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
      $data = json_decode($response, true);
      if (!empty($data['edits'])) {
        return $data['edits'][0]['replacement'] ?? null;
      }
    }

    return self::basicSpellCheck($text);
  }


  private static function basicSpellCheck($text)
  {
    $pdo = self::$database;

    // Fetch all item names from the database
    $query = "SELECT item_name FROM item";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $all_items = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $best_match = null;
    $highest_similarity = 0;

    foreach ($all_items as $item) {
      similar_text(strtolower($text), strtolower($item), $percent);
      if ($percent > $highest_similarity) {
        $highest_similarity = $percent;
        $best_match = $item;
      }
    }

    // Set a confidence threshold (e.g., 75% similarity)
    return ($highest_similarity > 75) ? $best_match : null;
  }
}
