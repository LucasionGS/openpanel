<?php
namespace OpenPanel\core\db;

/**
 * Abstract class to represent a database model. This class should be extended by all models.
 */
abstract class Model {
  /**
   * The table name of the model.
   */
  protected static string $table;

  /**
   * The primary key of the model.
   */
  protected static string $primaryKey = "id";

  /**
   * The fields of the model.
   */
  protected static array $fields;

  /**
   * The constructor for the model.
   */
  public function __construct() {}

  /**
   * Create array of data from the model.
   */
  public function toArray(): array {
    $data = [];
    foreach (static::$fields as $field) {
      $data[$field] = $this->$field;
    }
    return $data;
  }

  /**
   * Create a new model from an array of data.
   * 
   * @param array $data The data to create the model from.
   * @return static The new model.
   */
  public static function from(array $data): static {
    $model = new static();
    foreach ($data as $key => $value) {
      $model->$key = $value;
    }
    return $model;
  }

  /**
   * Get all the records from the model.
   * 
   * @return static[] An array of all the records.
   */
  public static function all(): array {
    $db = Database::getInstance();
    $result = $db->query("SELECT * FROM " . static::getTable());
    return array_map(function($row) {
      return static::from($row);
    }, $result);
  }

  /**
   * Select records from the model.
   * @param array|string $fields The fields to select.
   * @param array|string $where The where clause.
   * @param int $limit The limit.
   * @param int $offset The offset.
   * @return static[] An array of the records.
   */
  public static function select(array|string $fields = "*", array|string $where = [], int $limit = 0, int $offset = 0): array {
    $db = Database::getInstance();
    if (is_string($fields)) { $fields = [$fields]; }
    if (is_string($where)) { $where = [$where]; }
    $fields = implode(", ", $fields);
    
    $where = implode(" AND ", array_map(function($key, $value) {
      return "$key = '$value'";
    }, array_keys($where), array_values($where)));

    if ($where) { $where = "WHERE $where"; }

    $limitStr = $limit > 0 ? "LIMIT $offset, $limit" : "";
    
    $result = $db->query("SELECT $fields FROM " . static::getTable() . " $where $limitStr");
    return array_map(function($row) {
      return static::from($row);
    }, $result);
  }

  public static function count(array|string $where = []): int {
    $db = Database::getInstance();
    if (is_string($where)) { $where = [$where]; }
    $where = implode(" AND ", array_map(function($key, $value) {
      return "$key = '$value'";
    }, array_keys($where), array_values($where)));

    if ($where) { $where = "WHERE $where"; }

    $result = $db->query("SELECT COUNT(*) as count FROM " . static::getTable() . " $where");
    return $result[0]["count"];
  }

  /**
   * Get a record by the primary key.
   * 
   * @param int $id The primary key.
   * @return static|null The record or null if not found.
   */
  public static function find(int $id): ?static {
    $db = Database::getInstance();
    $result = $db->query("SELECT * FROM " . static::getTable() . " WHERE " . static::getPrimaryKey() . " = $id");
    if (count($result) === 0) {
      return null;
    }
    return static::from($result[0]);
  }

  private static function toSQLValue(mixed $value): string {
    $db = Database::getInstance();
    if (is_int($value)) { return $value; }
    if (is_bool($value)) { return $value ? 1 : 0; }
    
    return "'". $db->connection->real_escape_string($value) ."'";
  }

  /**
   * Insert a record into the model.
   * 
   * @param array $data The data to insert.
   * @return bool True if the record was inserted, false otherwise.
   */
  public static function insert(array $data): bool {
    $db = Database::getInstance();
    $fields = implode(", ", array_keys($data));
    $values = implode(", ", array_map(function($value) {
      return self::toSQLValue($value);
    }, array_values($data)));
    return $db->query("INSERT INTO " . static::getTable() . " ($fields) VALUES ($values)");
  }

  /**
   * Update a record in the model.
   * 
   * @param int $id The primary key of the record.
   * @param array $data The data to update.
   * @return bool True if the record was updated, false otherwise.
   */
  public static function update(int $id, array $data): bool {
    $db = Database::getInstance();
    $fields = implode(", ", array_map(function($key, $value) use ($db) {
      return "$key = " . self::toSQLValue($value);
    }, array_keys($data), array_values($data)));
    return $db->query("UPDATE " . static::getTable() . " SET $fields WHERE " . static::getPrimaryKey() . " = $id");
  }

  /**
   * Delete a record from the model.
   * 
   * @param int $id The primary key of the record.
   * @return bool True if the record was deleted, false otherwise.
   */
  public static function delete(int $id): bool {
    $db = Database::getInstance();
    return $db->query("DELETE FROM " . static::getTable() . " WHERE " . static::getPrimaryKey() . " = $id");
  }

  /**
   * Truncate the model.
   */
  public static function truncate() {
    $db = Database::getInstance();
    return $db->query("TRUNCATE TABLE " . static::getTable());
  }

  /**
   * Get the table name of the model.
   * 
   * @return string The table name.
   */
  public static function getTable(): string {
    return static::$table;
  }

  /**
   * Get the primary key of the model.
   * 
   * @return string The primary key.
   */
  public static function getPrimaryKey(): string {
    return static::$primaryKey;
  }
}