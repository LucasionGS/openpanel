<?php
use OpenPanel\core\logging\Logger;
use OpenPanel\core\webhost\Host;
use OpenPanel\core\webhost\HostAddonForm;

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
  $templates = Host::getTemplates(); // ["basic_page" => "/var/www/openpanel/core/templates/nginx/basic_page"]
  $template = $_GET["template"] ?? null;
  if (!$template) {
    $template = array_key_first($templates);

    if (!$template) {
      Logger::error("No templates found");
      return;
    }
  }

  // Ensure the template exists
  if (!isset($templates[$template])) {
    Logger::error("Template not found: $template");
    return;
  }
  
  $host = null;
  $addons = [];
  
  if (isset($editId) && is_numeric($editId)) {
    // $host = $sql->select("hosts", intval($editId))[0] ?? null;
    $host = Host::find(intval($editId));

    if (!$host) {
      Logger::error("No host found with ID: $editId");
      return;
    }

    $addons = $host->parseAddons();
  }

  // Get all $_POST that is prefixed with addon_
  foreach ($_POST as $key => $value) {
    if (strpos($key, "addon_") === 0) {
      $addons[substr($key, 6)] = $value;
    }
  }

  $addons["template"] = $template; // Fixed addon
  
  
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
                "portssl" => $portssl,
                "addons" => json_encode($addons)
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
                "portssl" => $portssl ?: null,
                "addons" => json_encode($addons)
              ])) {

                // Setup
                $host = Host::select("*", ["hostname" => $hostname], 1)[0];
                $host->setup($addons);
                
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
        <!-- Templates -->
        <div>
          <label for="template">Template</label>
          <br>
          <select name="template" id="template" onchange="window.location.href = '/hosting/edit?id=<?= $host ? $host->id : '' ?>&template=' + this.value">
            <?php
            foreach ($templates as $key => $value) {
              ?>
              <option value="<?= $key ?>" <?= $template === $key ? "selected" : "" ?>><?= $key ?></option>
              <?php
            }
            ?>
          </select>
        </div>
        
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
          <label for="portssl">Port (SSL)</label>
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

        <?php
          // Get the form.php from the templete, if it exists
          $formPath = $templates[$template] . "/form.php";
          if (file_exists($formPath)) {
            require $formPath;

            if (function_exists("form")) {
              echo "<hr>";
              echo "<br>";
              $form = new HostAddonForm();
              form($form);
              $form->render($addons);
            }
          }
          // if (isset($templates[$template])) {
          // }
        ?>
        
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