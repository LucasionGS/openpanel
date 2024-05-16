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
    do {
      $uuid = uniqid();
      $existingUsers = self::select("auth_token", ["auth_token" => $uuid], 1);
      if (count($existingUsers ?? []) === 0) {
        break;
      }
    }
    while (true);
    return $db->insert("users", [
      "username" => $username,
      "password" => self::encrypt($password),
      "auth_token" => $uuid
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
      $_SESSION["user"] = $user["auth_token"];
      return true;
    }
    return false;
  }

  private static ?User $currentUser = null;
  public static function current() {
    if (self::$currentUser == null && isset($_SESSION["user"])) {
      self::$currentUser = User::fromToken($_SESSION["user"]);
    }
    return self::$currentUser;
  }

  public static function fromToken(string $token) {
    $user = self::select("*", ["auth_token" => $token])[0] ?? null;
    return $user;
  }

  /**
   * Ensure that the user is authenticated. If not, redirect to the login page.
   */
  public static function ensureAuthenticated() {
    // var_dump($_SERVER);
    $path = $path = explode("?", $_SERVER["REQUEST_URI"])[0];
    if (!self::current() && $path !== "/login") {
      header("Location: /login");
      exit;
    }
  }
}