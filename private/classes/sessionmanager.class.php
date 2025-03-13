<?php
// sessionmanager.php
require_once __DIR__ . '/dbsessionhandler.class.php';;

class SessionManager
{
  protected $pdo;
  protected $handler;
  protected $table;

  public function __construct(PDO $pdo, $table = 'sessions')
  {
    $this->pdo = $pdo;
    $this->table = $table;
    $this->handler = new DBSessionHandler($this->pdo, $this->table);
    session_set_save_handler($this->handler, true);
    session_start();
  }

  // Optionally, add helper methods for your sessions.
  public function regenerate()
  {
    session_regenerate_id(true);
  }

  public function get($key)
  {
    return $_SESSION[$key] ?? null;
  }

  public function set($key, $value)
  {
    $_SESSION[$key] = $value;
  }

  public function destroySession()
  {
    session_destroy();
  }
}
