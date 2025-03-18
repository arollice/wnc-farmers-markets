<?php

class DatabaseObject
{
  static protected $database;
  static protected $table_name = "";
  static protected $db_columns = [];
  static protected $primary_key = "id"; // Default; override in subclasses
  public $errors = [];

  // Allowed table names for generic fetches (prevents SQL injection)
  static protected $allowed_tables = [
    'vendor',
    'vendor_item',
    'market',
    'vendor_market',
    'region',
    'item',
    'season',
    'policy_info',
    'users',
    'currency',
    'vendor_currency',
    'state'
  ];

  // Set the PDO database connection
  static public function set_database($database)
  {
    self::$database = $database;
  }

  public static function get_database()
  {
    return self::$database;
  }

  // Execute an SQL query with optional parameters and return an array of instantiated objects
  static public function find_by_sql($sql, $params = [])
  {
    $stmt = self::$database->prepare($sql);
    $stmt->execute($params);
    $object_array = [];
    while ($record = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $object_array[] = static::instantiate($record);
    }
    return $object_array;
  }

  // Retrieve all records from the table as objects
  static public function find_all_as_objects()
  {
    $sql = "SELECT * FROM " . static::$table_name;
    return static::find_by_sql($sql);
  }

  // Retrieve a single record by primary key using a prepared statement
  static public function find_by_id($id)
  {
    $sql = "SELECT * FROM " . static::$table_name . " WHERE " . static::$primary_key . " = :id LIMIT 1";
    $stmt = self::$database->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    return $record ? static::instantiate($record) : false;
  }

  // Instantiate an object based on a database record array
  static protected function instantiate($record)
  {
    $object = new static;
    foreach ($record as $property => $value) {
      if (property_exists($object, $property)) {
        $object->$property = $value;
      }
    }
    return $object;
  }

  // Validate the object before saving; add custom validations as needed
  protected function validate()
  {
    $this->errors = [];
    // Add custom validations here
    return $this->errors;
  }

  // Insert a new record into the database using a prepared statement
  protected function create()
  {
    $this->validate();
    if (!empty($this->errors)) {
      error_log(print_r($this->errors, true));
      return false;
    }

    $attributes = $this->attributes();
    $columns = array_keys($attributes);
    $placeholders = array_map(function ($col) {
      return ':' . $col;
    }, $columns);

    $sql = "INSERT INTO " . static::$table_name . " (" . join(', ', $columns) . ") ";
    $sql .= "VALUES (" . join(', ', $placeholders) . ")";

    $stmt = self::$database->prepare($sql);
    foreach ($attributes as $key => $value) {
      if (is_null($value)) {
        $stmt->bindValue(':' . $key, null, PDO::PARAM_NULL);
      } else {
        $stmt->bindValue(':' . $key, $value);
      }
    }
    $result = $stmt->execute();
    if ($result) {
      $primary_key = static::$primary_key;
      $this->$primary_key = self::$database->lastInsertId();
    }
    return $result;
  }


  // Update an existing record using a prepared statement
  protected function update()
  {
    $this->validate();
    if (!empty($this->errors)) {
      return false;
    }

    $attributes = $this->attributes();
    $attribute_pairs = [];
    foreach ($attributes as $key => $value) {
      $attribute_pairs[] = "{$key} = :{$key}";
    }

    $sql = "UPDATE " . static::$table_name . " SET " . join(', ', $attribute_pairs);
    $sql .= " WHERE " . static::$primary_key . " = :id LIMIT 1";

    $stmt = self::$database->prepare($sql);
    foreach ($attributes as $key => $value) {
      $stmt->bindValue(':' . $key, $value);
    }
    $stmt->bindValue(':id', $this->{static::$primary_key}, PDO::PARAM_INT);
    $result = $stmt->execute();
    return $result;
  }

  // Save the object: update if primary key exists, otherwise create
  public function save()
  {
    $primary_key = static::$primary_key;
    if (isset($this->$primary_key)) {
      return $this->update();
    } else {
      return $this->create();
    }
  }

  // Merge given attributes into the object
  public function merge_attributes($args = [])
  {
    foreach ($args as $key => $value) {
      if (property_exists($this, $key) && !is_null($value)) {
        $this->$key = $value;
      }
    }
  }

  // Return an associative array of attributes based on db_columns (excluding the primary key)
  public function attributes()
  {
    $attributes = [];
    foreach (static::$db_columns as $column) {
      if ($column == static::$primary_key) {
        continue;
      }
      $attributes[$column] = $this->$column;
    }
    return $attributes;
  }

  // Since prepared statements handle sanitization, return the attributes directly
  protected function sanitized_attributes()
  {
    return $this->attributes();
  }

  // Delete the record from the database using a prepared statement
  public function delete()
  {
    $sql = "DELETE FROM " . static::$table_name . " WHERE " . static::$primary_key . " = :id LIMIT 1";
    $stmt = self::$database->prepare($sql);
    $stmt->bindValue(':id', $this->{static::$primary_key}, PDO::PARAM_INT);
    $result = $stmt->execute();
    return $result;
  }

  /********************
   * Utility Functions
   ********************/

  // Fetch all records from a validated table (prevents SQL injection by whitelisting allowed table names)
  static public function fetch_all_from_table($table)
  {
    if (!in_array($table, self::$allowed_tables)) {
      die("Invalid table name: " . htmlspecialchars($table));
    }

    $stmt = self::$database->query("SELECT * FROM " . $table);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Escape user input for output safety
  static public function escape($input)
  {
    return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
  }

  // Display a table (for debugging or admin views)
  static public function displayTable($table)
  {
    $rows = self::fetch_all_from_table($table);
    if (!$rows) {
      echo "<p>No records found.</p>";
      return;
    }

    echo "<table border='1'>";
    echo "<tr>";
    foreach (array_keys($rows[0]) as $column) {
      echo "<th>" . self::escape($column) . "</th>";
    }
    echo "</tr>";

    foreach ($rows as $row) {
      echo "<tr>";
      foreach ($row as $cell) {
        echo "<td>" . self::escape($cell) . "</td>";
      }
      echo "</tr>";
    }
    echo "</table>";
  }
}
