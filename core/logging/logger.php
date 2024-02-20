<?php
class Logger {
  // Gray info box
  public static function log($message, ?string $title = null) {
    $title ??= "Info";
    ?>
    <div class="info-box">
      <h2><?= $title ?></h2>
      <p><?= $message ?></p>
    </div>
    <?php
  }

  // Yellow info box
  public static function warn($message, ?string $title = null) {
    $title ??= "Warning";
    ?>
    <div class="info-box info-box-warning">
      <h2><?= $title ?></h2>
      <p><?= $message ?></p>
    </div>
    <?php
  }

  // Yellow info box
  public static function error($message, ?string $title = null) {
    $title ??= "Error";
    ?>
    <div class="info-box info-box-error">
      <h2><?= $title ?></h2>
      <p><?= $message ?></p>
    </div>
    <?php
  }
}