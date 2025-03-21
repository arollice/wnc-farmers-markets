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
}
