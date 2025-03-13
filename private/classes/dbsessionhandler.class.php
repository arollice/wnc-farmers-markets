<?php

use PDO;
use SessionHandlerInterface;

class DBSessionHandler implements SessionHandlerInterface
{
  protected $pdo;
  protected $table;

  public function __construct(PDO $pdo, $table = 'sessions')
  {
    $this->pdo = $pdo;
    $this->table = $table;
  }

  // Called when a session is opened.
  public function open($save_path, $session_name): bool
  {
    return true;
  }

  // Called when a session is closed.
  public function close(): bool
  {
    return true;
  }

  // Reads the session data from the database.
  public function read($session_id): string
  {
    $stmt = $this->pdo->prepare("SELECT data FROM {$this->table} WHERE id = :id");
    $stmt->bindValue(':id', $session_id, PDO::PARAM_STR);
    $stmt->execute();
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      return $row['data'];
    }
    return '';
  }

  // Writes the session data to the database.
  public function write($session_id, $session_data): bool
  {
    $timestamp = time();
    $stmt = $this->pdo->prepare(
      "REPLACE INTO {$this->table} (id, data, last_access) VALUES (:id, :data, :last_access)"
    );
    $stmt->bindValue(':id', $session_id, PDO::PARAM_STR);
    $stmt->bindValue(':data', $session_data, PDO::PARAM_STR);
    $stmt->bindValue(':last_access', $timestamp, PDO::PARAM_INT);
    return $stmt->execute();
  }

  // Destroys the session data for a given session id.
  public function destroy($session_id): bool
  {
    $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE id = :id");
    $stmt->bindValue(':id', $session_id, PDO::PARAM_STR);
    return $stmt->execute();
  }

  // Garbage collection to delete old sessions.
  public function gc(int $maxlifetime)
  {
    $old = time() - $maxlifetime;
    $stmt = $this->pdo->prepare("DELETE FROM {$this->table} WHERE last_access < :old");
    $stmt->bindValue(':old', $old, PDO::PARAM_INT);
    if ($stmt->execute()) {
      return $stmt->rowCount();  // Return the number of deleted rows
    } else {
      return false;
    }
  }
}
