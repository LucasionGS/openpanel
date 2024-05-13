<?php
namespace OpenPanel\core\auth;

class User {
  public static function create(string $username, string $password) {
    $db = \OpenPanel\core\db\Database::getInstance();
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
    $db = \OpenPanel\core\db\Database::getInstance();
    $user = $db->query("SELECT * FROM users WHERE username = '$username'")[0];
    if ($user && self::verify($password, $user["password"])) {
      $_SESSION["user"] = $user;
      return true;
    }
    return false;
  }
}