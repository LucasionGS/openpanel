<?php
require(__DIR__ . "/../core/logging/logger.php");

function head()
{
  ?>
  <title>OpenPanel - Hosting</title>
  <?php
}

function page()
{
  require(__DIR__ . "/../core/sql.php");
  try {
    if (isset($_POST["action"])) {
      $action = $_POST["action"];
      switch ($action) {
        case "create":
          if (isset($_POST["hostname"]) && isset($_POST["port"])) {
            $hostname = $_POST["hostname"];
            $port = $_POST["port"];
            $sql->query("INSERT INTO hosts (hostname, port) VALUES ('$hostname', '$port')");
            Logger::storeLog("Created new host: $hostname:$port");
          }
          break;

        case "delete":
          if (isset($_POST["id"])) {
            $id = $_POST["id"];
            if ($sql->query("DELETE FROM hosts WHERE id = $id")) {
              Logger::storeLog("Deleted host with ID: $id");
            }
            else {
              Logger::error("Failed to delete host with ID: $id");
            }
          }
          break;

        default:
          break;
      }
    }

    $hosts = $sql->query("SELECT * FROM hosts");
  } catch (\Throwable $th) {
    $hosts = [];
    Logger::error($th->getMessage());
  }
  ?>

  <div>
    <form method="post">
      <input type="hidden" name="action" value="create">
      <!-- <div style="display: flex; gap: 16px;"> -->
      <div class="bg-silver">
        <div>
          <label for="hostname">Hostname</label>
          <br>
          <input type="text" name="hostname" id="hostname" placeholder="Hostname" required>
        </div>
        <div>
          <label for="hostname">Port</label>
          <br>
          <input type="number" name="port" id="port" placeholder="Port" max="65565" min="1" required value="80">
        </div>
      </div>
      <input type="submit" value="Create new host">
    </form>

    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Hostname</th>
          <th>Port</th>
          <th>Port SSL</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($hosts as $host): ?>
          <tr>
            <td>
              <?= $host["id"] ?>
            </td>
            <td>
              <?= $host["hostname"] ?>
            </td>
            <td>
              <?= $host["port"] ?>
            </td>
            <td>
              <?= isset($host["portssl"]) ? $host["portssl"] : "443" ?>
            </td>
            <td>
              <form method="post">
                <input type="hidden" name="id" value="<?= $host["id"] ?>">
                <button type="submit" name="action" value="edit">
                  Edit
                </button>
                <button type="submit" name="action" value="delete" class="btn-red">
                  Delete
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php
}