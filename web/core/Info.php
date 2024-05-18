<?php
namespace OpenPanel\core;

class Info {
  public static string $version = "1.0.0";
  public static string $build = "1";

  public static function versionToValue(string $version): int {
    $parts = explode(".", $version);
    // Ensure that the version is at least 3 parts long
    while (count($parts) < 3) {
      $parts[] = "0";
    }
    
    $value = 0;
    for ($i = 0; $i < count($parts); $i++) {
      $value += intval($parts[$i]) * pow(1000, count($parts) - $i - 1);
    }
    return $value;
  }

  public static function valueToVersion(int $value): string {
    $version = "";
    while ($value > 0) {
      $version = ($value % 1000) . ($version ? "." : "") . $version;
      $value = intval($value / 1000);
    }
    return $version;
  }

  public static function compareVersions(string $version1, string $version2): int {
    $value1 = self::versionToValue($version1);
    $value2 = self::versionToValue($version2);
    return $value1 - $value2;
  }
}