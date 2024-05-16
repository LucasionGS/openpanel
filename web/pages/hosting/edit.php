<?php
use OpenPanel\core\logging\Logger;
use OpenPanel\core\webhost\Host;

function head()
{
  ?>
  <title>OpenPanel - Hosting</title>
  <?php
}

function page()
{
  $sql = \OpenPanel\core\db\Database::getInstance();

  $editId = $_GET["id"] ?? null;
  
  $host = null;
  
  if (isset($editId) && is_numeric($editId)) {
    // $host = $sql->select("hosts", intval($editId))[0] ?? null;
    $host = Host::find(intval($editId));

    if (!$host) {
      Logger::error("No host found with ID: $editId");
      return;
    }
  }
  
  try {
    if (isset($_POST["action"])) {
      $action = $_POST["action"];

      $id = $_POST["id"] ?? null;
      $hostname = $_POST["hostname"] ?? null;
      $port = $_POST["port"] ?? null;
      $portssl = $_POST["portssl"] ?? null;
      
      switch ($action) {
        case "create":
          if (
            isset($hostname)
            && isset($port)
            && isset($portssl)
          ) {

            if ($id) {
              $id = intval($id);
              if ($sql->update("hosts", [
                "hostname" => $hostname,
                "port" => $port,
                "portssl" => $portssl
              ], $id)) {
                Logger::storeLog("Updated host with ID: $id");
                header("Location: /hosting");
              }
              else {
                Logger::error("Failed to update host with ID: $id");
              }
            }
            else {
              // $sql->query("INSERT INTO hosts (hostname, port) VALUES ('$hostname', '$port')");
              // if ($sql->insert("hosts", [
              //   "hostname" => $hostname,
              //   "port" => $port,
              //   "portssl" => $portssl ?: null
              // ])) {
              if (Host::insert([
                "hostname" => $hostname,
                "port" => $port,
                "portssl" => $portssl ?: null
              ])) {

                // Setup
                $host = Host::select("*", ["hostname" => $hostname], 1)[0];
                $host->setup();
                
                Logger::storeLog("Created new host: $hostname:$port");
                header("Location: /hosting");
              }
              else {
                Logger::error("Failed to create new host: $hostname:$port");
              }
            }
            
          }
          break;
      }
    }
  } catch (\Throwable $th) {
    Logger::error($th->getMessage());
  }
  ?>

  <div>
    <form method="post">
      <?php
      if ($host) {
        ?>
        <input type="hidden" name="id" value="<?= $host->id ?>">
        <?php
      }
      ?>
      
      <input type="hidden" name="action" value="create">
      <!-- <div style="display: flex; gap: 16px;"> -->
      <div>
        <div>
          <label for="hostname">Hostname</label>
          <br>
          <input
            value="<?= $host ? $host->hostname : "" ?>"
            type="text"
            name="hostname"
            id="hostname"
            placeholder="Hostname"
            required
          >
        </div>
        
        <div>
          <label for="port">Port</label>
          <br>
          <input
            value="<?= $host ? $host->port : "80" ?>"
            type="number"
            name="port"
            id="port"
            placeholder="Port"
            max="65565"
            min="1"
            required
          >
        </div>

        <div>
          <label for="portssl">portssl</label>
          <br>
          <input
            value="<?= $host ? $host->portssl : "443" ?>"
            type="number"
            name="portssl"
            id="portssl"
            placeholder="Port SSL"
            max="65565"
            min="1"
            required
          >
        </div>
      </div>
      <?php
      if ($host) {
        ?>
        <input type="submit" value="Update host">
        <?php
      }
      else {
        ?>
        <input type="submit" value="Create new host">
        <?php
      }
      ?>
    </form>
  </div>
  <?php
}