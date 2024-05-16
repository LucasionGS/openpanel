<?php
use OpenPanel\core\db\Database;
use OpenPanel\core\Layout;
use OpenPanel\core\logging\Logger;

require(__DIR__ . "/../config.php");
Layout::$meta->margin = false;
Layout::$meta->title = "OpenPanel - Databases";
if ($CFG->debug) {
  Layout::$meta->nav = [
    "OP/Logs" => ["/database?database=openpanel&table=logs", __DIR__ . "/../core/icons/database.svg"],
  ];
}
// Layout::$meta->navbarEnabled = false;


function page()
{
  global $CFG;
  $currentCon = new Database($CFG->db_host, $CFG->db_user, $CFG->db_password);

  $created = $_GET["created"] ?? null;
  $dropped = $_GET["dropped"] ?? null;
  $action = $_REQUEST["action"] ?? null;

  if ($dropped) {
    ?>
    <div style="background-color: #00aa00; color: white; padding: 8px; margin-bottom: 8px;">
      Database "<?= $dropped ?>" dropped successfully
    </div>
    <?php
  }

  if ($created) {
    ?>
    <div style="background-color: #00aa00; color: white; padding: 8px; margin-bottom: 8px;">
      Database "<?= $created ?>" created successfully
    </div>
    <?php
  }

  if (isset($action)) {
    switch ($action) {
      case 'create_db':
        $database = $_GET["database"];

        if (!$database) {
          Logger::error("No database name provided");
          return;
        }
        
        $currentCon->query("CREATE DATABASE $database;");
        header("Location: /database?created=$database");
        return;
    }
  }

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
        if ($action == "drop") {
          if (isset($_POST["confirm"]) && $_POST["confirm"] == "1") {
            $currentCon->query("DROP DATABASE $database;");
            header("Location: /database?dropped=$database");
            return;
          }
          else {
            ?>
            <div style="background-color: #aa0000; color: white; padding: 8px; margin-bottom: 8px;">
              Are you sure you want to drop the database "<?= $database ?>"?
              <form method="post">
                <input type="hidden" name="action" value="drop">
                <input type="hidden" name="confirm" value="1">
                <button type="submit">Yes</button>
              </form>
            </div>
            <?php
          }
        }

        $currentCon->setDatabase($database);
        $tables = $currentCon->query("SHOW TABLES;");
      } else {
        Logger::error("Database does not exist");
        return;
      }

    }
  } catch (\Throwable $th) {
    // $databases = [];
    Logger::error($th->getMessage());
  }
  ?>
  <div style="display: flex; height: 100%;">
    <div style="background-color: darkslategray; padding: 8px; box-sizing: border-box; margin-right: 8px; height: 100%">
      <form>
        <input type="text" name="database" placeholder="Database">
        <button type="submit" name="action" value="create_db">Create</button>
      </form>
      <hr>
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
                <a style="padding-left: 8px; color: white; text-decoration: <?= $selected ? "underline" : "none" ?>;" href="?database=<?= $database ?>&table=<?= $name ?>">
                  <?= $name ?>
                </a>
                <br>
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
      <h1>
        <?php
        if ($database) {
          ?>
          Databases - <?= $database ?>
          <br>
          <!-- Drop button -->
          <form method="post" style="display: inline;">
            <input type="hidden" name="action" value="drop">
            <button type="submit" class="btn-danger">Drop</button>
          </form>
          <hr>
          <?php
        }
        else {
          ?>
          Databases
          <?php
        }
        ?>
      </h1>
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