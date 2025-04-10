<?php

class Utils
{
  public static function displayName($url)
  {
    if (empty($url)) {
      return 'Unknown';
    }

    $cleanUrl = str_replace('/public/', '/', $url);
    $path = parse_url($cleanUrl, PHP_URL_PATH);
    $baseName = basename($path);

    $name = preg_replace('/\.php$/', '', $baseName);

    $name = ucwords(str_replace(['-', '_'], ' ', $name));

    if (strcasecmp($name, 'Index') === 0 || $url === '/web289/public/') {
      return 'Home';
    }
    return $name;
  }

  public static function setFlashMessage($type, $message)
  {
    $_SESSION["{$type}_message"] = $message;
  }

  public static function displayFlashMessages()
  {
    if (isset($_SESSION['success_message'])) {
      echo "<div style='padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; margin-bottom:10px;'>";
      echo htmlspecialchars($_SESSION['success_message']);
      echo "</div>";
      unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
      echo "<div style='padding:10px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; margin-bottom:10px;'>";
      echo htmlspecialchars($_SESSION['error_message']);
      echo "</div>";
      unset($_SESSION['error_message']);
    }
  }

  public static function displaySpellSuggestion()
  {
    if (isset($_SESSION['spell_suggestion'])) {
      $original   = htmlspecialchars($_SESSION['spell_suggestion']['original']);
      $suggestion = htmlspecialchars($_SESSION['spell_suggestion']['suggestion']);
      echo "<div style='padding:10px; background:#fff3cd; color:#856404; border:1px solid #ffeeba; margin-bottom:10px;'>";
      echo "Did you mean <strong>$suggestion</strong> instead of <strong>$original</strong>? ";
      echo "<form action='vendor-dashboard.php' method='POST' style='display:inline; margin-right:10px;'>
                <input type='hidden' name='item_name' value='$original'>
                <input type='hidden' name='confirm_spell' value='decline'>
                <button type='submit' name='add_item_btn'>Ignore Suggestion</button>
              </form>";
      echo "<form action='vendor-dashboard.php' method='POST' style='display:inline;'>
                <input type='hidden' name='item_name' value='$original'>
                <input type='hidden' name='confirm_spell' value='accept'>
                <button type='submit' name='add_item_btn'>Accept Suggestion</button>
              </form>";
      echo "</div>";
    }
  }

  /**
   * Validates the file size of an uploaded file.
   *
   * @param array $file The $_FILES entry for the file.
   * @param int $maxFileSize Maximum allowed file size in bytes.
   * @return bool True if the file size is within the allowed limit, false otherwise.
   */
  public static function validateFileSize($file, $maxFileSize)
  {
    if (isset($file) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
      return $file['size'] <= $maxFileSize;
    }
    // If no file is uploaded, consider it valid.
    return true;
  }

  /**
   * Recursively sanitize an array by stripping HTML and PHP tags.
   *
   * @param mixed $data The input data (array or string).
   * @param string $allowed_tags Optional list of allowed tags.
   * @return mixed The sanitized data.
   */
  public static function sanitize($data, $allowed_tags = '')
  {
    if (is_array($data)) {
      $sanitized = [];
      foreach ($data as $key => $value) {
        $sanitized[$key] = self::sanitize($value, $allowed_tags);
      }
      return $sanitized;
    } elseif (is_string($data)) {
      return strip_tags($data, $allowed_tags);
    }
    return $data;
  }
}
