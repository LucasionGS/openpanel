<?php
namespace OpenPanel\core;
use OpenPanel\core\db\Model;

class Settings extends Model {
  protected static string $table = "settings";
  protected static string $primaryKey = "id";
  protected static array $fields = ["id", "name", "value"];

  public int $id;
  public string $name;
  public string $value;

  public static function has(string $key): ?string {
    $c = self::count(["name" => $key]);
    return $c > 0;
  }

  public static function get(string $key): ?string {
    $res = self::select("value", ["name" => $key], 1)[0] ?? null;
    return $res ? $res->value : null;
  }

  public static function set(string $key, string $value) {
    $existing = self::select("value", ["name" => $key], 1)[0] ?? null;
    if ($existing) {
      self::update($existing->id, ["value" => $value]);
      return;
    }
    self::insert(["name" => $key, "value" => $value]);
  }
}