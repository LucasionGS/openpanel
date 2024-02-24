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

if ($sections[1] === "_") {
  $newPath = "/" . join("/", array_slice($sections, 2));
  echo __DIR__ . "/extensions" . $newPath;
  layout(__DIR__ . "/extensions" . $newPath);
  exit;
}

layout(__DIR__ . "/pages" . $path);
