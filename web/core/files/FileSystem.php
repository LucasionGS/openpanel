<?php
namespace OpenPanel\core\files;

class FileSystem {
  /**
   * Raw import of a file. Typically used for CSS or SVG files.
   */
  public static function import($path) {
    $content = file_get_contents($path);
    return $content;
  }
}