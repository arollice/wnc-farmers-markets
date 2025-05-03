<?php

//Validates that the given value is a positive integer.
function validateId($id)
{
  return filter_var($id, FILTER_VALIDATE_INT) && $id > 0;
}

// Validates that the vendor ID is a positive integer.
function validateVendorId($vendor_id)
{
  return validateId($vendor_id);
}


// Validates that the market ID is a positive integer & also checks if the market ID exists in the database.

function validateMarketId($market_id)
{
  // integer check
  if (!validateId($market_id)) {
    return false;
  }

  // Verify it’s in the markets table
  $pdo  = DatabaseObject::get_database();
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM market WHERE market_id = ?");
  $stmt->execute([$market_id]);
  return $stmt->fetchColumn() > 0;
}


//Validates login fields
function validateLoginFields($username, $password)
{
  $errors = [];
  if (empty(trim($username))) {
    $errors[] = "Username is required.";
  }
  if (empty(trim($password))) {
    $errors[] = "Password is required.";
  }
  return $errors;
}

//Admin-new-market validation

/**
 * Validate new region inputs.
 * @return array  field-keyed errors
 */
function validateNewRegion($name, $lat, $lng): array
{
  $errors = [];
  if (trim($name) === '') {
    $errors['new_region_name'] = "Name can't be blank.";
  }
  if (!is_numeric($lat) || $lat < -90 || $lat > 90) {
    $errors['new_region_lat'] = 'Latitude must be a number between -90 and 90.';
  }
  if (!is_numeric($lng) || $lng < -180 || $lng > 180) {
    $errors['new_region_lng'] = 'Longitude must be a number between -180 and 180.';
  }
  return $errors;
}

/**
 * Validate main market fields.
 * @param array $data  raw $_POST
 * @return array  field-keyed errors
 */
function validateMarketFields(array $data): array
{
  $errors = [];
  // Name
  if (trim($data['market_name'] ?? '') === '') {
    $errors['market_name'] = "Market name can't be blank.";
  } elseif (mb_strlen($data['market_name']) > 100) {
    $errors['market_name'] = 'Market name must be under 100 characters.';
  }

  // Region
  if (empty($data['region_id']) || !validateId($data['region_id'])) {
    $errors['region_id'] = 'Please select a valid region.';
  }

  // City
  if (trim($data['city'] ?? '') === '') {
    $errors['city'] = "City can't be blank.";
  }

  // State
  if (empty($data['state_id']) || !validateId($data['state_id'])) {
    $errors['state_id'] = 'Please select a state.';
  }

  // ZIP code
  if (!preg_match('/^\d{5}$/', $data['zip_code'] ?? '')) {
    $errors['zip_code'] = 'ZIP must be exactly 5 digits.';
  }

  // Hours
  $open  = $data['market_open']  ?? '';
  $close = $data['market_close'] ?? '';
  if ($open && !preg_match('/^\d{2}:\d{2}$/', $open)) {
    $errors['market_open'] = 'Invalid open time.';
  }
  if ($close && !preg_match('/^\d{2}:\d{2}$/', $close)) {
    $errors['market_close'] = 'Invalid close time.';
  }
  if ($open && $close && $open >= $close) {
    $errors['market_close'] = 'Closing time must be after opening time.';
  }

  return $errors;
}

/**
 * Validate schedule inputs (days + optional season).
 */
function validateSchedule(array $days, $season_id, $last_day): array
{
  $errors = [];
  if (empty($days)) {
    $errors['market_days'] = 'Please select at least one market day.';
  }
  if ($season_id && !validateId($season_id)) {
    $errors['season_id'] = 'Invalid season selection.';
  }
  if ($last_day) {
    $d = DateTime::createFromFormat('Y-m-d', $last_day);
    if (!($d && $d->format('Y-m-d') === $last_day)) {
      $errors['last_day_of_season'] = 'Enter a valid date.';
    }
  }
  return $errors;
}
//vendor-register form

/**
 * Ensure at least one market is selected and each ID is valid.
 *
 * @param array $marketIds
 * @return array  keyed errors
 */
function validateMarketSelection(array $marketIds): array
{
  $errors = [];
  // Must pick at least one
  if (count($marketIds) < 1) {
    $errors['market_ids'] = 'Please select at least one market to attend.';
    return $errors;
  }
  // Ensure each is a positive integer
  foreach ($marketIds as $id) {
    if (! validateMarketId((int)$id)) {
      $errors['market_ids'] = 'One of the selected markets is invalid.';
      break;
    }
  }
  return $errors;
}

/**
 * Ensure at least one payment method is selected and each ID is valid.
 *
 * @param array $paymentIds
 * @return array  keyed errors
 */
function validatePaymentSelection(array $paymentIds): array
{
  $errors = [];
  if (count($paymentIds) < 1) {
    $errors['accepted_payments'] = 'Please select at least one accepted payment method.';
    return $errors;
  }
  foreach ($paymentIds as $id) {
    if (! validateId((int)$id)) {
      $errors['accepted_payments'] = 'One of the selected payment methods is invalid.';
      break;
    }
  }
  return $errors;
}

/**
 * Validate the core vendor‐registration inputs.
 *
 * @param string $vendor_name
 * @param string $vendor_website
 * @param string $username
 * @param string $email
 * @param string $password
 * @param string $password_confirm
 * @return array  keyed error messages
 */
function validateVendorRegistrationFields(
  string $vendor_name,
  string $vendor_website,
  string $username,
  string $email,
  string $password,
  string $password_confirm
): array {
  $errors = [];

  // 1) Business name
  if ($vendor_name === '') {
    $errors['vendor_name'] = "Business name can't be blank.";
  } elseif (mb_strlen($vendor_name) > 100) {
    $errors['vendor_name'] = "Business name must be under 100 characters.";
  }

  // 2) Username
  if ($username === '') {
    $errors['username'] = "Username is required.";
  } elseif (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
    $errors['username'] = "Username must be 3–20 chars: letters, numbers, or underscores.";
  }

  // 3) Email
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = "A valid email address is required.";
  }

  // 4) Passwords
  if ($password === '' || $password_confirm === '') {
    $errors['password'] = "Both password fields are required.";
  } elseif ($password !== $password_confirm) {
    $errors['password'] = "Passwords do not match.";
  } else {
    if (strlen($password) < 8) {
      $errors['password'] = "Password must be at least 8 characters long.";
    }
    if (
      !preg_match('/[A-Z]/', $password) ||
      !preg_match('/[a-z]/', $password) ||
      !preg_match('/\d/',   $password)
    ) {
      $errors['password'] = "Password must include uppercase, lowercase, and a number.";
    }
  }

  // 5) Website (optional)
  if ($vendor_website !== '' && ! filter_var($vendor_website, FILTER_VALIDATE_URL)) {
    $errors['vendor_website'] = "Please enter a valid URL (include http:// or https://).";
  }

  // 6) Description (optional)
  if (mb_strlen($vendor_description ?? '') > 255) {
    $errors['vendor_description'] = "Description must be at most 255 characters.";
  }

  return $errors;
}
