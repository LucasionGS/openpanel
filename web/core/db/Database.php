<?php
namespace OpenPanel\core\db;
use OpenPanel\core\logging\Logger;
class Database {
  private $host;
  private $user;
  private $password;
  private $database;
  public function setDatabase(string $database) {
    $this->database = $database;
    if ($this->connection) {
      $this->connection->select_db($database);
    }
  }
  public $connection;

  public function __construct($host, $user, $password, $database = "") {
    $this->host = $host;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;
    $this->reconnect();
  }

  public function reconnect() {
    $this->connection = new \mysqli($this->host, $this->user, $this->password, $this->database);
    if ($this->connection->connect_error) {
      throw new \Exception("Connection failed: " . $this->connection->connect_error);
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
      throw new \Exception("Query failed: " . $this->connection->error);
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
    if ($this->connection) {
      $this->connection->close();
    }
  }

  public function select(string $table, array|string|int $where = [], $limit = 0, $offset = 0) {
    $sqlWhereParams = null;
    if (is_numeric($where)) {
      $where = ["id" => $where];
    }
    else if (is_string($where)) {
      // Use raw where clause, example: "id = 1"
    }

    if (is_array($where)) {
      $sqlWhereParams = array_map(function($key, $value) {
        return "`$key` = '$value'";
      }, array_keys($where), $where);
    }

    $sql = "SELECT * FROM $table";

    if (
      isset($where) &&
      (
        (
          isset($sqlWhereParams) &&
          (count($sqlWhereParams) > 0)
        )
        || is_string($where)
      )
    ) {
      $sql .= " WHERE " . ($sqlWhereParams ? implode(" AND ", $sqlWhereParams) : $where);
    }

    if ($limit > 0) {
      if ($offset > 0) {
        $sql .= " LIMIT $offset, $limit";
      }
      else {
        $sql .= " LIMIT $limit";
      }
    }

    return $this->query($sql);
  }

  public function insert(string $table, array $data) {
    $keys = array_keys($data);
    $sqlKeysParams = array_map(function($key) {
      return "`$key`";
    }, $keys);

    $values = array_values($data);
    $sqlValueParams = array_map(function($value) {
      if (gettype($value) === "string") {
        return "'" . $this->connection->real_escape_string($value) . "'";
      }
      else if (is_null($value)) {
        return "NULL";
      }
      return $value;
    }, $values);

    $sql = "INSERT INTO $table (" . implode(", ", $sqlKeysParams) . ") VALUES (" . implode(", ", $sqlValueParams) . ")";
    $result = $this->query($sql);

    return (bool)$result;
  }

  public function update(string $table, array $data, $where) {
    $keys = array_keys($data);

    $values = array_values($data);
    $sqlValueParams = array_map(function($value) {
      if (gettype($value) === "string") {
        return "'" . $this->connection->real_escape_string($value) . "'";
      }
      else if (is_null($value)) {
        return "NULL";
      }
      return $value;
    }, $values);

    $sql = "UPDATE $table SET " . implode(", ", array_map(function($key, $value) {
      return "`$key` = $value";
    }, $keys, $sqlValueParams));

    if (is_numeric($where)) {
      $sql .= " WHERE id = $where";
    }
    else if (is_string($where)) {
      $sql .= " WHERE $where";
    }
    else if (is_array($where)) {
      $sql .= " WHERE " . implode(" AND ", array_map(function($key, $value) {
        return "`$key` = '$value'";
      }, array_keys($where), $where));
    }

    $result = $this->query($sql);

    return (bool)$result;
  }

  static ?Database $instance = null;
  public static function getInstance() {
    if (self::$instance === null) {
      global $CFG;
      include(__DIR__ . "/../../config.php");
      try {
        self::$instance = new Database($CFG->db_host, $CFG->db_user, $CFG->db_password, $CFG->db_database);
      } catch (\Throwable $th) {
        Logger::error(
          $th->getMessage()
          . "<hr>" .
          "This is likely due to the database not being set up. Visit <a href=\"/install\">the setup page</a> to set it up."
          . "<br>" .
          "<a href=\"/install\"><button>Begin setup</button></a>"
        , "Database Error", false);
        exit(1);
      }

      if (self::$instance->connection->connect_error) {
        Logger::error(
          self::$instance->connection->connect_error
          . "<hr>" .
          "This is likely due to the database not being set up. Visit <a href=\"/install\">the setup page</a> to set it up."
          . "<br>" .
          "<a href=\"/install\"><button>Begin setup</button></a>"
        , "Database Error", false);
        exit(1);
      }
    }
    return self::$instance;
  }
}