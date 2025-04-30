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

  /**
   * Generates a random CAPTCHA image, stores the code in $_SESSION['captcha_code'],
   * sends the PNG headers/output, and exits.
   *
   * @param int  $width   Width of the image in px
   * @param int  $height  Height of the image in px
   * @param int  $length  Number of characters in the CAPTCHA code
   * @param int  $fontSize  Font size (GD units) for the text
   * @param string $fontPath  Full path to a .ttf font on your server
   * @return void  — this method will exit after sending the image
   */
  public static function generateCaptchaImage(
    int $width     = 120,
    int $height    = 40,
    int $length    = 6,
    int $fontSize  = 18,
    string $fontPath = PRIVATE_PATH . '/fonts/Roboto-Regular.ttf'

  ): void {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    if (!is_readable($fontPath)) {
      throw new \RuntimeException("CAPTCHA font not found or unreadable at: $fontPath");
    }

    // Generate & store code
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < $length; $i++) {
      $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    $_SESSION['captcha_code'] = $code;

    // Create image canvas
    $img = imagecreatetruecolor($width, $height);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $fg = imagecolorallocate($img, 50, 100, 180);
    $noise = imagecolorallocate($img, 200, 200, 200);

    imagefilledrectangle($img, 0, 0, $width, $height, $bg);

    // Add noise
    for ($i = 0; $i < ($width * $height) / 3; $i++) {
      imagesetpixel($img, random_int(0, $width), random_int(0, $height), $noise);
    }

    // Draw the code
    $x = 10;
    $y = ($height + $fontSize) / 2;
    foreach (str_split($code) as $letter) {
      imagettftext(
        $img,
        $fontSize,
        random_int(-10, 10),
        $x,
        $y,
        $fg,
        $fontPath,
        $letter
      );
      $x += $fontSize * 0.9;
    }

    // Output and clean up
    header('Content-Type: image/png');
    imagepng($img);
    imagedestroy($img);
  }

  /**
   * Checks a user-supplied CAPTCHA code against the one in session.
   * Unsets the stored code so it can’t be reused.
   *
   * @param string|null $userInput  The $_POST['captcha_code'] value
   * @return bool  True if match (case-insensitive), false otherwise
   */
  public static function checkCaptcha(?string $userInput): bool
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }
    $stored = $_SESSION['captcha_code'] ?? '';
    unset($_SESSION['captcha_code']);
    return $stored !== '' && strcasecmp($stored, trim($userInput)) === 0;
  }

  /**
   * Validate a CSRF token from a POST against the one in session.
   *
   * @param string|null $token The raw $_POST['csrf_token'] value
   * @return bool True if valid, false otherwise
   */
  public static function validateCsrf(?string $token): bool
  {
    if (session_status() !== PHP_SESSION_ACTIVE) {
      session_start();
    }

    $stored = $_SESSION['csrf_token'] ?? '';
    // Use hash_equals for timing-safe comparison
    return is_string($token)
      && is_string($stored)
      && hash_equals($stored, $token);
  }
}
