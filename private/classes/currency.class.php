<?php
//require_once('databaseobject.class.php');

class Currency extends DatabaseObject
{
  static protected $table_name = "currency";
  static protected $db_columns = ['currency_id', 'currency_name'];
  static protected $primary_key = 'currency_id';

  public $currency_id;
  public $currency_name;
}
