<?php
class Season extends DatabaseObject
{
  static protected $table_name  = 'season';
  static protected $db_columns  = ['season_id', 'season_name'];
  static protected $primary_key = 'season_id';

  public $season_id;
  public $season_name;

  public static function fetchAll(): array
  {
    return static::find_all_as_objects();
  }
}
