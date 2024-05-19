<?php
namespace OpenPanel\core\webhost;
use OpenPanel\core\db\Database;
use OpenPanel\core\db\Model;
use OpenPanel\core\Extension;
use OpenPanel\core\logging\Logger;

class Host extends Model {
  public int $id;
  public string $hostname;
  public int $port;
  public int $portssl;

  private static string $vhostsAvailableDir = "/etc/nginx/sites-available";
  private static string $vhostsEnabledDir = "/etc/nginx/sites-enabled";
  private static string $webRoot = "/var/www/vhosts";
  private static string $logsDir = "/var/www/logs";
  private static string $webTemplates = __DIR__ . "/../templates/nginx";

  static string $table = "hosts";
  protected static array $fields = ["id", "hostname", "port", "portssl"];

  public function getEditUrl() {
    return "/hosting/edit?id=$this->id";
  }

  public function getDeleteUrl() {
    return "/hosting/delete?id=$this->id";
  }

  public function getWebRoot() {
    return static::$webRoot . "/" . $this->hostname;
  }

  public function getLogsDir() {
    return static::$logsDir . "/" . $this->hostname;
  }

  /**
   * Get all available templates.
   * @return string[] An array of template names.
   */
  public function getTemplates(): array {
    // Get core templates
    $coreTemps = array_diff(scandir(static::$webTemplates), [".", ".."]);
    $exts = Extension::select("*", ["enabled" => 1], 0);
    $extTemps = [];
    foreach ($exts as $ext) {
      $webTempExtDir = $ext->getPath() . "/web-templates";
      $temps = array_diff(scandir($webTempExtDir), [".", ".."]);
    }

    return array_merge($coreTemps, $extTemps);
  }

  public function setup() {
    
    if (!file_exists(static::$vhostsAvailableDir)) {
      mkdir(static::$vhostsAvailableDir, 0755, true);
    }

    if (!file_exists(static::$vhostsEnabledDir)) {
      mkdir(static::$vhostsEnabledDir, 0755, true);
    }

    if (!file_exists(static::$webRoot)) {
      mkdir(static::$webRoot, 0755, true);
    }

    if (!file_exists(static::$logsDir)) {
      mkdir(static::$logsDir, 0755, true);
    }

    $this->createVhost();
  }

  public function createVhost(array $addons = []) {
    $template = $addons["template"] ?? "basic_page";
    // $template = $addons["template"] ?? "php_page";
    // $template = $addons["template"] ?? "moodle404";
    $logsDir = $this->getLogsDir();

    // $templates = $this->getTemplates();


    // In case setup.php exists in the template directory, run the setup function from it.
    $setupPath = static::$webTemplates . "/$template/setup.php";
    if (file_exists($setupPath)) {
      require $setupPath;

      if (function_exists("setup")) {
        "setup"($this, $addons); // Called before for optionally changing $addons
      }
    }

    $phpVersion = $addons["phpVersion"] ?? "8.1";
    $server_name = $this->hostname;
    $root = $addons["root"] ?? $this->getWebRoot();
    
    // Addons
    if (isset($addons["aliases"]) && count($addons["aliases"]) > 0) {
      $server_name .= " " . join(" ", $addons["aliases"]);
    }
    
    $vhost = static::parseVhost(
      file_get_contents(static::$webTemplates . "/$template/$template.conf"),
      [
        "port" => $this->port,
        "server_name" => $server_name,
        "root" => $root,
        "php_version" => $phpVersion,
        "logs_dir" => $logsDir
      ]
    );

    $vhostPath = static::$vhostsAvailableDir . "/" . $this->hostname;
    file_put_contents($vhostPath, $vhost);

    // Create directory
    mkdir($root, 0755, true);
    mkdir($logsDir, 0755, true);
    
    $this->enable();
    $this->reloadNginx();
  }

  public function deleteVhost(bool $deleteFiles = false) {
    $this->disable();
    $vhostPath = static::$vhostsAvailableDir . "/" . $this->hostname;
    if (file_exists($vhostPath)) {
      unlink($vhostPath);
    }

    if ($deleteFiles) {
      $root = $this->getWebRoot();
      $logDir = $this->getLogsDir();
      exec("rm -rf $root");
      exec("rm -rf $logDir");
    }

    $this->reloadNginx();
  }

  public function reloadNginx() {
    exec("systemctl reload nginx");
  }

  public static function fromHostname(string $hostname) {
    $host = static::select("*", ["hostname" => $hostname], 1)[0] ?? null;
    return $host;
  }

  public function enable() {
    $path = static::$vhostsEnabledDir . "/" . $this->hostname;

    if (file_exists($path)) {
      return;
    }

    symlink(static::$vhostsAvailableDir . "/" . $this->hostname, $path);
  }

  public function disable() {
    $path = static::$vhostsEnabledDir . "/" . $this->hostname;

    if (file_exists($path)) {
      unlink($path);
    }
  }

  /**
   * Parse a vhost template with variables.
   * A variable is defined as {{variableName}} in the template. Replaced with value from ["variableName" => "value"] in the variables array.
   * @return string The parsed vhost content.
   */
  public static function parseVhost(string $configContent, array $variables) {
    $configContent = str_replace(array_map(function($key) {
      return "{{".$key."}}";
    }, array_keys($variables)), array_values($variables), $configContent);
    return $configContent;
  }
}