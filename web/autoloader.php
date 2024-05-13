<?php
// Autoloader
spl_autoload_register(function ($class) {
  $prefix = 'OpenPanel\\';
  $base_dir = __DIR__ . '/';
  $len = strlen($prefix);
  if (strncmp($prefix, $class, $len) !== 0) {
    return;
  }
  $relative_class = substr($class, $len);

  $parts = explode("/", str_replace('\\', '/', $relative_class));
  $className = array_pop($parts);
  $relative_path = implode("/", $parts) . '/' . $parts[count($parts) - 1];

  $filepath = $base_dir . $relative_path;
  $file = $filepath . '.php';
  $file_alternative = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

  // echo $class . "<br>";
  // echo $file . "<br>";
  // echo $file_alternative . "<br>";
  // exit;

  try {
    if (file_exists($file)) {
      require $file;
      if (!class_exists($class)) {
        throw new \Exception("Class not found: $class");
      }
    } else if (file_exists($file_alternative)) {
      require $file_alternative;
      if (!class_exists($class)) {
        throw new \Exception("Class not found: $class");
      }
    }
  } catch (\Throwable $th) {
    require(__DIR__ . "/core/logging/logger.php");
    OpenPanel\core\logging\Logger::error(
      "bla bla bla bla $class no existo",
      "Class not found: $class"
    );
    exit;
  }
});