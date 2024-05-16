<?php
namespace OpenPanel\core;

use OpenPanel\core\db\Database;
use OpenPanel\core\db\Model;
use OpenPanel\core\logging\Logger;
use OpenPanel\core\Settings;
use OpenPanel\core\Info;

class Extension extends Model {
  public int $id;
  public string $name;
  public bool $enabled;

  public static function getExtensionFolder() {
    return realpath(__DIR__ . "/../extensions");
  }

  protected static string $table = "extensions";
  protected static array $fields = ["id", "name", "enabled"];
  
  public static function enable(string $name) {
    $ref = self::select("id", ['name' => $name], 1)[0];
    if ($ref === null) {
      self::install($name, true);
      return;
    }
    self::update($ref->id, ['enabled' => true]);
  }

  public static function disable(string $name) {
    $ref = self::select("id", ['name' => $name], 1)[0];
    if ($ref === null) {
      return;
    }
    self::update($ref->id, ['enabled' => false]);
  }

  public static function isEnabled(string $name): bool {
    $ref = self::select("enabled", ['name' => $name], 1)[0];
    if ($ref === null) {
      return false;
    }
    return $ref->enabled;
  }

  public static function install(string $name, bool $enabled = false) {
    // $sql = Database::getInstance();
    // $sql->insert("extensions", ['name' => $name, 'enabled' => $enabled]);
    self::insert(['name' => $name, 'enabled' => $enabled]);
  }

  public static function uninstall(string $name) {
    // $sql = Database::getInstance();
    $ref = self::select("id", ['name' => $name], 1)[0];
    if ($ref === null) {
      return;
    }
    self::delete($ref->id);
  }

  /**
   * Get a list of extensions from the disk
   * @return string[]
   */
  public static function getExtensionListFromDisk() {
    $pth = self::getExtensionFolder();
    if ($pth) {
      $files = scandir($pth);
      $exts = [];
      foreach ($files as $file) {
        if ($file == "." || $file == "..") {
          continue;
        }
        $exts[] = $file;
      }
      return $exts;
    }
    return [];
  }

  public static function refresh() {
    $files = self::getExtensionListFromDisk();
    foreach ($files as $file) {
      if ($file == "." || $file == "..") {
        continue;
      }
      $exists = self::count(['name' => $file]) > 0;
      if (!$exists) {
        self::install($file);
        // echo "Installed extension: $file\n";
      }
      else {
        // echo "Extension already installed: $file\n";
      }
    }
  }
}