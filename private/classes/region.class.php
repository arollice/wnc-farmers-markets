<?php
//require_once('databaseobject.class.php');

class Region extends DatabaseObject
{
  static protected $table_name = "region";
  static protected $db_columns = ['region_id', 'region_name'];
  static protected $primary_key = 'region_id';

  public $region_id;
  public $region_name;
}
