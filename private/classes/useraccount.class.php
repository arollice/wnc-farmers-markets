<?php
require_once('databaseobject.class.php');
require_once('vendor.class.php');

class UserAccount extends DatabaseObject
{
  static protected $table_name = "user_account";
  static protected $db_columns = [
    'user_id',
    'username',
    'password_hash',
    'email',
    'role',
    'vendor_id',
    'created_at',
    'last_login',
    'is_active'
  ];
  static protected $primary_key = 'user_id';

  public $user_id;
  public $username;
  public $password_hash;
  public $email;
  public $role;
  public $vendor_id;
  public $created_at;
  public $last_login;
  public $is_active;

  // If the user is linked to a vendor, fetch that vendor's information
  public function get_vendor()
  {
    $sql = "SELECT * FROM vendor WHERE vendor_id = :vendor_id LIMIT 1";
    $params = [':vendor_id' => $this->vendor_id];
    $result = Vendor::find_by_sql($sql, $params);
    return !empty($result) ? array_shift($result) : false;
  }
}
