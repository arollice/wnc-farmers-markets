<?php
// Turn on error display
ini_set('display_errors',        '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Bootstrap your config so PRIVATE_PATH is defined
require_once __DIR__ . '/../private/config.php';

// Point at the same Roboto font
$font = PRIVATE_PATH . '/fonts/Roboto-Regular.ttf';
if (! is_readable($font)) {
  die("Font not found at: $font");
}

// Send a PNG
header('Content-Type: image/png');

// Create a tiny image
$img    = imagecreatetruecolor(120, 40);
$white  = imagecolorallocate($img, 255, 255, 255);
$black  = imagecolorallocate($img,   0,  0,  0);
imagefilledrectangle($img, 0, 0, 120, 40, $white);

// Try to draw some text
$result = imagettftext(
  $img,        // image
  18,          // font size
  0,           // angle
  10,
  30,      // x, y
  $black,      // color
  $font,       // font file
  'HELLO'      // text
);

// If imagettftext() fails, $result will be false
if ($result === false) {
  imagedestroy($img);
  die("imagettftext() failed—likely GD is missing FreeType support.");
}

// Output and cleanup
imagepng($img);
imagedestroy($img);
