<?php
class State extends DatabaseObject
{
  /**
   * Fetch all states (ID + name).
   *
   * @return array [ ['state_id'=>1,'state_name'=>'Alabama'], … ]
   */
  public static function fetchAllStates(): array
  {
    $db   = DatabaseObject::get_database();
    $sql  = "SELECT state_id, state_name FROM state ORDER BY state_name";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
