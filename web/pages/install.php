<?php
require(__DIR__ . "/../core/logging/logger.php");

function head() {
  ?>
  <title>OpenPanel - Installation</title>
  <?php
}

function page() {
  Logger::log("Looks like you have installation setup! Go through the form below to create your instance.", "Installing OpenPanel");
  
  // $is_docker = getenv("OPENPANEL_ENVIRONMENT") === "docker";
  $is_docker = true;
  
  $db_host = $is_docker ? "mysql" : $_POST["db_host"];
  $db_user = $is_docker ? "openpanel" : $_POST["db_user"];
  $db_password = $is_docker ? "openpanel" : $_POST["db_password"];
  $db_database = $is_docker ? "openpanel" : $_POST["db_database"];
  
  $admin_user = $_POST["admin_user"];
  $admin_password = $_POST["admin_password"];
  
  
  if ($_POST["action"] == "finalize" && isset($db_host) && isset($db_user) && isset($db_password) && isset($db_database)) {
    $config = '
  <?php
  $CFG = new stdClass();
  $CFG->debug = true;
  
  if ($CFG->debug) {
    ini_set(\'display_errors\', 1);
    ini_set(\'display_startup_errors\', 1);
    error_reporting(E_ALL);
  }
  
  $CFG->db_host     = "mysql";
  $CFG->db_user     = "openpanel";
  $CFG->db_password = "openpanel";
  $CFG->db_database = "openpanel";
  ';
    
    if (!file_put_contents(__DIR__ . "/../config.php", $config)) {
      Logger::warn(htmlspecialchars($config), "Failed to write to config.php. Please create the file and paste the following content:");
    }
  
    require(__DIR__ . "/../core/db/initial.php");
    $db = new Database($db_host, $db_user, $db_password); // Create a new connection to the database
    $mysqli = $db->connection;
    if ($mysqli->connect_error) {
      Logger::error("Connection failed: " . $mysqli->connect_error);
    } else {
      try {
        initial_database_setup($mysqli, $db_database);
        
        $sql = "INSERT INTO users (username, password) VALUES ('$admin_user', '$admin_password')";
        if ($db->query($sql) !== TRUE) {
          Logger::error("Error creating admin user: " . $mysqli->error);
        }
        
        Logger::log("Database setup successfully");
      } catch (\Throwable $th) {
        Logger::error($th->getMessage());
      }
    }
  }
  global $db_host, $db_user, $db_password, $db_database, $admin_user, $admin_password;
?>
<div>
  <?php if (!($db_host != "" && $db_user != "" && $db_password != "" && $db_database != "")): ?>
    <form method="post">
      <input type="text" name="db_host" id="db_host" placeholder="Database Host" value="<?= $db_host ?>">
      <desc>
        The host of the database, usually localhost.
      </desc>

      <input type="text" name="db_user" id="db_user" placeholder="Database User" value="<?= $db_user ?>">
      <desc>
        The user which will be used to connect to the database.
      </desc>

      <input type="password" name="db_password" id="db_password" placeholder="Database Password"
        value="<?= $db_password ?>">
      <desc>
        The password of the database user.
      </desc>

      <input type="text" name="db_database" id="db_database" placeholder="Database Name" value="<?= $db_database ?>">
      <desc>
        The name of the database. This should exist already, and the user entered above should have full access to it.
      </desc>

      <input type="hidden" name="action" value="create">
      <br>
      <input type="submit" value="Create new host">
    </form>
  <?php elseif (!($admin_user && $admin_password)): ?>
    <form method="post">
      <input type="text" name="admin_user" id="admin_user" placeholder="Admin User" value="<?= $admin_user ?>">
      <desc>
        The username of the admin user.
      </desc>

      <input type="password" name="admin_password" id="admin_password" placeholder="Admin Password"
        value="<?= $admin_password ?>">
      <desc>
        The password of the admin user.
      </desc>


      <input type="hidden" name="db_host" value="<?= $db_host ?>">
      <input type="hidden" name="db_user" value="<?= $db_user ?>">
      <input type="hidden" name="db_password" value="<?= $db_password ?>">
      <input type="hidden" name="db_database" value="<?= $db_database ?>">
      <input type="hidden" name="action" value="finalize">
      <input type="submit" value="Create new host">
    </form>
  <?php else: ?>
    <!-- Loading -->
    <p>Please wait while we setup the database...</p>
    <p><?= $db_host ?></p>
    <p><?= $db_user ?></p>
    <p><?= $db_password ?></p>
    <p><?= $db_database ?></p>
    <p><?= $admin_user ?></p>
    <p><?= $admin_password ?></p>
  <?php endif; ?>
</div>
<?php
}
?>