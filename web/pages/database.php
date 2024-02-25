<?php
require_once(__DIR__ . "/../core/db/database.php");
require(__DIR__ . "/../core/logging/logger.php");

$meta = [
  "margin" => false,
  "title" => "OpenPanel - Databases",
  "nav" => [
    "OpenPanel/Logs" => ["/database?database=openpanel&table=logs", __DIR__ . "/../core/icons/database.svg"],
  ]
];

function page()
{
  // global $currentCon;
  require(__DIR__ . "/../config.php");
  $currentCon = new Database($CFG->db_host, $CFG->db_user, $CFG->db_password);
  try {
    $database = null;
    $databases = $currentCon->query("SHOW DATABASES;");
    // Filter
    foreach ($databases as $db) {
      foreach ($databases as $db) {
        if (
          $db["Database"] === "information_schema"
          || $db["Database"] === "performance_schema"
          || $db["Database"] === "mysql"
          || $db["Database"] === "sys"
          || ($db["Database"] === "openpanel" && !$CFG->debug)
        ) {
          unset($databases[array_search($db, $databases)]);
        }
      }
    }
    $tables = [];
    if (isset($_GET["database"])) {
      $database = $_GET["database"];

      // Make sure the DB exists
      $exists = false;
      foreach ($databases as $db) {
        if ($db["Database"] === $database) {
          $exists = true;
          break;
        }
      }

      if ($exists) {
        $currentCon->setDatabase($database);
        $tables = $currentCon->query("SHOW TABLES;");
      } else {
        Logger::error("Database does not exist");
      }

    }
  } catch (\Throwable $th) {
    // $databases = [];
    Logger::error($th->getMessage());
  }
  ?>
  <div style="display: flex; height: 100%;">
    <div style="background-color: darkslategray; padding: 8px; box-sizing: border-box; margin-right: 8px; height: 100%">
      <?php
      foreach ($databases as $databaseEntry) {
        ?>
        <div style="border-bottom: 1px beige solid;">
          <a style="color: white; text-decoration: none;" href="?database=<?= $databaseEntry["Database"] ?>">
            <h4 style="margin: 0;">
              <?= $databaseEntry["Database"] ?>
            </h4>
          </a>
          <?php
          if ($databaseEntry["Database"] === $database) {
            ?>
            <ul style="margin: 0;">
              <?php
              foreach ($tables as $table) {
                $name = $table["Tables_in_$database"];
                $selected = ($_GET["table"] ?? "") === $name;
                ?>
                <a style="color: white; text-decoration: <?= $selected ? "underline" : "none" ?>;" href="?database=<?= $database ?>&table=<?= $name ?>">
                  <?= $name ?>
                </a>
                <?php
              }
              ?>
            </ul>
            <?php
          }
          ?>
        </div>
        <?php
      }
      ?>
    </div>
    <?php

    $table = null;
    if (isset($_GET["table"])) {
      $table = $_GET["table"];
    }

    $columns = [];
    if ($table) {
      $columns = $currentCon->query("SHOW COLUMNS FROM $table;");
    }

    $rows = [];
    if ($table) {
      $rows = $currentCon->query("SELECT * FROM $table;");
    }
    ?>

    <div>
      <h1>Databases</h1>
      <table>
        <thead>
          <tr>
            <?php
            foreach ($columns as $column) {
              ?>
              <th>
                <?= $column["Field"] . " [" . $column["Type"] . "]" ?>
              </th>
              <?php
            }
            ?>
          </tr>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <?php
              foreach ($columns as $column) {
                ?>
                <td>
                  <?= $row[$column["Field"]] ?>
                </td>
                <?php
              }
              ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php
}