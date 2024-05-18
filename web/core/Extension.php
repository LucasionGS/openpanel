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
  public string $display_name;
  public string $description;
  public string $version;
  public string $installed_version;
  public string $author;

  public function getPath() {
    return realpath(__DIR__ . "/../extensions/" . $this->name);
  }

  public static function getExtensionFolder() {
    return realpath(__DIR__ . "/../extensions");
  }

  public static function getByName(string $name) {
    return self::select("*", ['name' => $name], 1)[0] ?? null;
  }

  protected static string $table = "extensions";
  protected static array $fields = ["id", "name", "enabled", "display_name", "description", "version", "installed_version", "author"];
  
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
    $existing = self::select("id", ['name' => $name], 1)[0] ?? null;
    
    // Read the extension's info file
    $pth = realpath(__DIR__ . "/../extensions/" . $name . "/$name.yml");
    if ($pth) {
      $info = yaml_parse_file($pth);
      $display_name = $info["name"] ?? $name;
      $description = $info["description"] ?? "";
      $version = $info["version"] ?? throw new \Exception("Extension $name does not have a version number");
      $author = $info["author"] ?? "<Unknown>";
    }
    else {
      throw new \Exception("Extension $name does not have an info file. ($name.yml)");
    }

    if ($existing) {
      self::update($existing->id, [
        'display_name' => $display_name,
        'description' => $description,
        'version' => $version,
        'author' => $author
      ]);
      return;
    }
    
    self::insert([
      'name' => $name,
      'enabled' => $enabled,
      'display_name' => $display_name,
      'description' => $description,
      'version' => $version,
      'installed_version' => $version,
      'author' => $author
    ]);
  }

  public static function uninstall(string $name) {
    // $sql = Database::getInstance();
    $ref = self::select("id", ['name' => $name], 1)[0];
    if ($ref === null) {
      return;
    }
    self::delete($ref->id);
  }

  public static function upgrade(string $name) {
    $ref = self::getByName($name);
    if ($ref === null || ($ref->installed_version == $ref->version)) {
      return;
    }
    $pth = realpath(__DIR__ . "/../extensions/" . $name . "/$name.yml");
    if ($pth) {
      $info = yaml_parse_file($pth);
      $version = $info["version"] ?? throw new \Exception("Extension $name does not have a version number");
    }
    else {
      throw new \Exception("Extension $name does not have an info file. ($name.yml)");
    }

    // Check for upgrade.php's existence
    $pth = realpath(__DIR__ . "/../extensions/" . $name . "/upgrade.php");
    if ($pth) {
      require $pth;
      if (function_exists("upgrade")) {
        "upgrade"(Info::versionToValue($ref->installed_version), Info::versionToValue($version));
      }
    }
    
    self::update($ref->
    id, ['installed_version' => $version]);
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
      self::install($file);
    }
  }
}