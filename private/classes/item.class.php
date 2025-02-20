<?php
require_once('databaseobject.class.php');

class Item extends DatabaseObject
{
  static protected $table_name = "item";
  static protected $db_columns = ['item_id', 'item_name'];
  static protected $primary_key = 'item_id';

  public $item_id;
  public $item_name;
}
