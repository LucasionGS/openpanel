<?php
require_once(__DIR__ . "/../core/db/database.php");
require(__DIR__ . "/../core/logging/logger.php");


function head() {
  ?>
  <title>OpenPanel - Databases</title>
  <?php
}

function page() {
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
      }
      else {
        Logger::error("Database does not exist");
      }
      
    }
  } catch (\Throwable $th) {
    // $databases = [];
    Logger::error($th->getMessage());
  }
  ?>
  <nav>
    <ul style="display: flex; gap: 16px;">
      <?php
      foreach ($databases as $databaseEntry) {
        ?>
        <a href="?database=<?= $databaseEntry["Database"] ?>">
          <li>
            <?php echo importSvg(__DIR__ . "/../core/icons/database.svg") ?>
            <h3><?= $databaseEntry["Database"] ?></h3>
            <?php
            if ($databaseEntry["Database"] === $database) {
              ?>
              <ul>
                <?php
                foreach ($tables as $table) {
                  ?>
                  <a href="?database=<?= $database ?>&table=<?= $table["Tables_in_$database"] ?>">
                    <li>
                      <?= $table["Tables_in_$database"] ?>
                    </li>
                  </a>
                  <?php
                }
                ?>
              </ul>
              <?php
            }
            ?>
          </li>
        </a>
        <?php
      }
      ?>
    </ul>
  </nav>
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

<?php
}