<?php
namespace OpenPanel;
use OpenPanel\core\auth\User;
session_start();

$path = $_SERVER["REQUEST_URI"];
$path = explode("?", $path)[0];
$sections = explode("/", $path);
try {
  require __DIR__ . "/config.php";
} catch (\Throwable $th) {
  if ($path !== "/install") {
    header("Location: /install");
    exit;
  }
}

if ($path !== "/install") {
  User::ensureAuthenticated();
}

use OpenPanel\core\Layout;
use OpenPanel\core\Extension;

function checkApiEndpoint($path) {
  $corePage = $path;
  $corePage = file_exists($corePage . ".php") ? $corePage . ".php" : $corePage . "/index.php";
  if (file_exists($corePage)) {
    require($corePage);
    return true;
  }
  return false;
}

// API -- First section is core's API,
// second section is the API endpoints defined in pages/api,
// third section is the API endpoints defined in extensions
if ($sections[1] === "api") {
  if (checkApiEndpoint(__DIR__ . "/core" . $path)) {exit;}
  else if (checkApiEndpoint(__DIR__ . "/pages" . $path)) {exit;}
  else {
    header("HTTP/1.1 404 Not Found");
    echo "404 Not Found: " . $path;
    exit;
  }
}

$extensionName = $sections[2] ?? "";
if ($sections[1] === "_" && $extensionName !== "" && Extension::isEnabled($extensionName)) {
  // Third section of API is extensions
  if (($sections[3] ?? "") === "api" && checkApiEndpoint(
    __DIR__ . "/extensions" . ("/" . join("/", array_slice($sections, 2)))
  )) { exit; }
  
  $newPath = "/" . join("/", array_slice($sections, 2));
  Layout::renderMain(__DIR__ . "/extensions" . $newPath);
  exit;
}

Layout::renderMain(__DIR__ . "/pages" . $path);
