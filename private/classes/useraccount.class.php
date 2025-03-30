<?php
//require_once('databaseobject.class.php');
//require_once('vendor.class.php');

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

  // Static register method to create a new user account.
  public static function register($data)
  {
    $user = new self();
    $user->username = $data['username'];
    // Expect a key 'password' and hash it here
    $user->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $user->email = $data['email'];
    $user->role = $data['role'];
    $user->vendor_id = $data['vendor_id'];
    $user->created_at = date("Y-m-d H:i:s");
    // last_login is not set at registration
    $user->is_active = 1;

    if ($user->save()) {
      return $user;
    } else {
      return false;
    }
  }

  public function updateLastLogin()
  {
    $this->last_login = date("Y-m-d H:i:s");
    return $this->save();
  }

  public static function find_by_username($username)
  {
    $sql = "SELECT * FROM " . static::$table_name . " WHERE username = :username LIMIT 1";
    $stmt = self::$database->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    return $record ? static::instantiate($record) : false;
  }

  public static function find_by_email($email)
  {
    $sql = "SELECT * FROM " . static::$table_name . " WHERE email = :email LIMIT 1";
    $stmt = self::$database->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    return $record ? static::instantiate($record) : false;
  }
}
