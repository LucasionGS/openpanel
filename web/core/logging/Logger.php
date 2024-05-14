<?php
namespace OpenPanel\core\logging;

class Logger {
  // Gray info box
  public static function log($message, ?string $title = null) {
    $title ??= "Info";
    ?>
    <div class="info-box">
      <h2><?= $title ?></h2>
      <pre><?= $message ?></pre>
    </div>
    <?php
  }

  // Yellow info box
  public static function warn($message, ?string $title = null, $storeDb = true) {
    $title ??= "Warning";
    ?>
    <div class="info-box info-box-warning">
      <h2><?= $title ?></h2>
      <pre><?= $message ?></pre>
    </div>
    <?php

    if ($storeDb) self::storeLog($message);
  }

  // Yellow info box
  public static function error($message, ?string $title = null, $storeDb = true) {
    $title ??= "Error";
    ?>
    <div class="info-box info-box-error">
      <h2><?= $title ?></h2>
      <pre><?= $message ?></pre>
    </div>
    <?php

    if ($storeDb) self::storeLog($message);
  }

  public static function storeLog($message) {
    $sql = \OpenPanel\core\db\Database::getInstance();
    $query = "INSERT INTO logs (message) VALUES ('".
      $sql->connection->escape_string($message)
    ."')";
    $sql->query($query);
  }
}