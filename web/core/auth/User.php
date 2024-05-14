<?php
namespace OpenPanel\core\auth;
use OpenPanel\core\db\Database;
use OpenPanel\core\db\Model;

class User extends Model {
  static string $table = "users";
  protected static array $fields = ["id", "username", "password"];

  public int $id;
  public string $username;
  public string $password;

  /**
   * Create a new user. Automatically encrypts the password.
   */
  public static function create(string $username, string $password) {
    $db = Database::getInstance();
    return $db->insert("users", [
      "username" => $username,
      "password" => self::encrypt($password)
    ]);
  }

  public static function encrypt(string $password) {
    return password_hash($password, PASSWORD_DEFAULT);
  }

  public static function verify(string $password, string $hash) {
    return password_verify($password, $hash);
  }

  public static function login(string $username, string $password) {
    $db = Database::getInstance();
    $user = $db->query("SELECT * FROM users WHERE username = '$username'")[0];
    if ($user && self::verify($password, $user["password"])) {
      $_SESSION["user"] = $user;
      return true;
    }
    return false;
  }

  public static function current() {
    // return $_SESSION["user"] ?? null;
    return static::find(1);
  }
}