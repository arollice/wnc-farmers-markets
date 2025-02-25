<?php
//require_once('databaseobject.class.php');

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
    $pdo = self::$database; // Use the inherited database connection

    // Fetch all item names from the database
    $item_query = "SELECT item_name FROM item";
    $stmt = $pdo->prepare($item_query);
    $stmt->execute();
    $all_items = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Common misspellings (Failsafe for words that don't match well)
    $manual_corrections = [
      'rbead' => 'bread',
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
      'darry' => 'dairy'
    ];

    // Check if the term has a manual correction
    if (array_key_exists(strtolower($search_term), $manual_corrections)) {
      $closest_match = $manual_corrections[strtolower($search_term)];
    } else {
      // Otherwise, find the best match dynamically
      $closest_match = null;
      $best_jaro_score = 0;
      $shortest_distance = PHP_INT_MAX;

      foreach ($all_items as $item) {
        $damerau_distance = self::damerauLevenshtein(strtolower($search_term), strtolower($item));
        $jaro_score = self::jaroWinkler(strtolower($search_term), strtolower($item));
        $metaphone_match = (metaphone($search_term) == metaphone($item));

        // Set adaptive threshold: Allow bigger distance for longer words
        $threshold = (strlen($search_term) <= 5) ? 3 : 4;

        // Damerau-Levenshtein distance-based correction
        if ($damerau_distance < $shortest_distance && $damerau_distance <= $threshold) {
          $closest_match = $item;
          $shortest_distance = $damerau_distance;
        }

        // Jaro-Winkler: If score > 0.85, prioritize
        if ($jaro_score > 0.85 && $jaro_score > $best_jaro_score) {
          $closest_match = $item;
          $best_jaro_score = $jaro_score;
        }

        // Metaphone phonetic matching
        if ($metaphone_match && $closest_match === null) {
          $closest_match = $item;
        }
      }
    }

    // Final search term (corrected or original)
    $final_search_term = ($closest_match !== null) ? $closest_match : $search_term;

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
      'suggested_term' => ($final_search_term !== $search_term) ? $final_search_term : null
    ];
  }




  /**
   * Damerau-Levenshtein Distance Algorithm
   * Handles:
   *  - Insertions
   *  - Deletions
   *  - Substitutions
   *  - Transpositions (swapped adjacent letters)
   */
  private static function damerauLevenshtein($str1, $str2)
  {
    $lenStr1 = strlen($str1);
    $lenStr2 = strlen($str2);

    if ($lenStr1 == 0) return $lenStr2;
    if ($lenStr2 == 0) return $lenStr1;

    // Create distance matrix
    $dist = array_fill(0, $lenStr1 + 1, array_fill(0, $lenStr2 + 1, 0));

    for ($i = 0; $i <= $lenStr1; $i++) {
      $dist[$i][0] = $i;
    }
    for ($j = 0; $j <= $lenStr2; $j++) {
      $dist[0][$j] = $j;
    }

    // Calculate distances
    for ($i = 1; $i <= $lenStr1; $i++) {
      for ($j = 1; $j <= $lenStr2; $j++) {
        $cost = ($str1[$i - 1] == $str2[$j - 1]) ? 0 : 1;

        $dist[$i][$j] = min(
          $dist[$i - 1][$j] + 1,      // Deletion
          $dist[$i][$j - 1] + 1,      // Insertion
          $dist[$i - 1][$j - 1] + $cost // Substitution
        );

        // Handle transpositions (swapped adjacent letters)
        if (
          $i > 1 && $j > 1 &&
          $str1[$i - 1] == $str2[$j - 2] &&
          $str1[$i - 2] == $str2[$j - 1]
        ) {
          $dist[$i][$j] = min($dist[$i][$j], $dist[$i - 2][$j - 2] + $cost);
        }
      }
    }

    return $dist[$lenStr1][$lenStr2];
  }

  private static function jaroWinkler($str1, $str2)
  {
    $s1_len = strlen($str1);
    $s2_len = strlen($str2);

    if ($s1_len === 0 || $s2_len === 0) return 0.0;

    $match_distance = (int) floor(max($s1_len, $s2_len) / 2) - 1;
    $matches = 0;
    $hash_s1 = array_fill(0, $s1_len, 0);
    $hash_s2 = array_fill(0, $s2_len, 0);

    for ($i = 0; $i < $s1_len; $i++) {
      $start = max(0, $i - $match_distance);
      $end = min($i + $match_distance + 1, $s2_len);

      for ($j = $start; $j < $end; $j++) {
        if ($hash_s2[$j] === 1 || $str1[$i] !== $str2[$j]) continue;
        $hash_s1[$i] = 1;
        $hash_s2[$j] = 1;
        $matches++;
        break;
      }
    }

    if ($matches === 0) return 0.0;

    $transpositions = 0;
    $k = 0;
    for ($i = 0; $i < $s1_len; $i++) {
      if ($hash_s1[$i] === 0) continue;
      while ($hash_s2[$k] === 0) $k++;
      if ($str1[$i] !== $str2[$k]) $transpositions++;
      $k++;
    }

    $transpositions /= 2;
    $match_score = (($matches / $s1_len) + ($matches / $s2_len) + (($matches - $transpositions) / $matches)) / 3;
    $prefix_len = 0;

    for ($i = 0; $i < min(4, $s1_len, $s2_len); $i++) {
      if ($str1[$i] !== $str2[$i]) break;
      $prefix_len++;
    }

    return $match_score + (0.1 * $prefix_len * (1 - $match_score));
  }
}
