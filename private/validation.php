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

//Validates that the market ID is a positive integer.
function validateMarketId($market_id)
{
  return validateId($market_id);
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

// Validates vendor registration fields: business details and login fields.
function validateVendorRegistrationFields($vendor_name, $vendor_website, $username, $email, $password, $password_confirm)
{
  $errors = [];
  if (empty(trim($vendor_name))) {
    $errors[] = "Business Name is required.";
  }
  if (!empty($vendor_website) && !filter_var($vendor_website, FILTER_VALIDATE_URL)) {
    $errors[] = "Please provide a valid URL for the business website.";
  }
  if (empty(trim($username))) {
    $errors[] = "Username is required.";
  }
  if (empty(trim($email)) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email is required.";
  }
  if (empty($password) || empty($password_confirm)) {
    $errors[] = "Both password fields are required.";
  } elseif ($password !== $password_confirm) {
    $errors[] = "Passwords do not match.";
  }
  return $errors;
}
