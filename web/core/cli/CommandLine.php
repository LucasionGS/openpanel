<?php

namespace OpenPanel\core\cli;
class CommandLine {
  /**
   * Execute a script in /var/www/scripts
   */
  public static function exec(string $script, array $args = []) {
    $script = escapeshellcmd($script);
    $args = array_map('escapeshellarg', $args);
    $args = implode(' ', $args);
    $command = "bash /var/www/scripts/$script.sh $args";
    exec($command, $output, $return);
    return [
      'output' => $output,
      'return' => $return
    ];
  }

  public static function list() {
    $scripts = [];
    $dir = opendir('/var/www/scripts');
    while ($file = readdir($dir)) {
      if (!str_ends_with($file, '.sh')) {
        continue;
      }
      $scripts[] = preg_replace("/\.sh$/", "", $file);
    }
    closedir($dir);
    return $scripts;
  }
}