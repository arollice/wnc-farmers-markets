<?php
class Region extends DatabaseObject
{
  static protected $table_name = "region";
  // Add latitude, longitude, and description (if applicable) to your columns
  static protected $db_columns = ['region_id', 'region_name', 'latitude', 'longitude'];
  static protected $primary_key = 'region_id';

  public $region_id;
  public $region_name;
  public $latitude;
  public $longitude;
  public $description;
}
