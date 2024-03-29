<?php
require(__DIR__ . "/core/db/autoload.php");
require(__DIR__ . "/core/styles/autoload.php");

require(__DIR__ . "/core/layout.php");

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

if ($sections[1] === "_") {
  $extensionName = $sections[2];
  // Third section of API is extensions
  if (($sections[3] ?? "") === "api" && checkApiEndpoint(
    __DIR__ . "/extensions" . ("/" . join("/", array_slice($sections, 2)))
  )) { exit; }
  
  $newPath = "/" . join("/", array_slice($sections, 2));
  layout(__DIR__ . "/extensions" . $newPath);
  exit;
}

layout(__DIR__ . "/pages" . $path);
