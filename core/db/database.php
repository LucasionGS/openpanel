<?php
class Database {
  private $host;
  private $user;
  private $password;
  private $database;
  private $connection;

  public function __construct($host, $user, $password, $database) {
    $this->host = $host;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;
    $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database);
    if ($this->connection->connect_error) {
      die("Connection failed: " . $this->connection->connect_error);
    }
  }

  /**
   * @param string $query
   * @return array|boolean
   * @example $db->query("SELECT * FROM users") returns an array of all users in the table 'users
   */
  public function query($query) {
    $result = $this->connection->query($query);
    if (!$result) {
      die("Query failed: " . $this->connection->error);
    }

    if (gettype($result) === "boolean") {
      return $result;
    }
    
    return $result->fetch_all(MYSQLI_ASSOC);
  }

  public function queryMap($query, $class) {
    $result = $this->query($query);
    return array_map(function($row) use ($class) {
      $inst = new $class();
      foreach ($row as $key => $value) {
        $inst->$key = $value;
      }
    }, $result);
  }

  public function close() {
    $this->connection->close();
  }
}