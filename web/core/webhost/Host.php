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
  private static string $webTemplates = __DIR__ . "/../../web-templates";

  static string $table = "hosts";
  protected static array $fields = ["id", "hostname", "port", "portssl"];

  public function getEditUrl() {
    return "/hosting/edit?id=$this->id";
  }

  public function getDeleteUrl() {
    return "/hosting/delete?id=$this->id";
  }

  public function getTemplates() {
    // Get core templates
    $coreTemps = array_diff(scandir(static::$webTemplates), [".", ".."]);
    $exts = Extension::select("*", ["enabled" => 1], 0);
    $extTemps = [];
    foreach ($exts as $ext) {
      $extTemps = array_merge($extTemps, array_diff(scandir($ext->getPath() . "/web-templates"), [".", ".."]));
    }
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

    $this->createVhost();
  }

  public function createVhost(array $addons = []) {
    $phpVersion = $addons["phpVersion"] ?? "8.1";
    $server_name = $this->hostname;
    $root = static::$webRoot . "/" . $this->hostname;
    $template = $addons["template"] ?? "default";

    // Addons
    if (isset($addons["aliases"]) && count($addons["aliases"]) > 0) {
      $server_name .= " " . join(" ", $addons["aliases"]);
    }
    
    $vhost = <<<VHOST
server {
  listen 80;
  server_name $server_name;
  root $root;
  index index.php;

  location / {
    try_files \$uri \$uri/ /index.php?\$query_string;
  }

  sendfile off;

  fastcgi_intercept_errors on;

  location ~ /\.ht {
    deny all;
  }

  location ~ \.php {
    fastcgi_pass unix:/run/php/php$phpVersion-fpm.sock;
    fastcgi_split_path_info ^((?U).+\.php)(/?.+)$;
    fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    fastcgi_param PATH_INFO \$fastcgi_path_info;
    fastcgi_param PATH_TRANSLATED \$document_root\$fastcgi_path_info;
    fastcgi_read_timeout 600s;
    fastcgi_send_timeout 600s;
    fastcgi_index index.php;
    include /etc/nginx/fastcgi_params;
  }
}
VHOST;

    $vhostPath = static::$vhostsAvailableDir . "/" . $this->hostname;
    file_put_contents($vhostPath, $vhost); // This works

    // Create directory
    mkdir($root, 0755, true);
    $this->enable();


    
    
    $this->reloadNginx();
  }

  public function deleteVhost() {
    $this->disable();
    $vhostPath = static::$vhostsAvailableDir . "/" . $this->hostname;
    if (file_exists($vhostPath)) {
      unlink($vhostPath);
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
}