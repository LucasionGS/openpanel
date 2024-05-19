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
  if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    Logger::error("No numeric ID provided");
    return;
  }

  $id = intval($_GET["id"]);
  // $host = $sql->select("hosts", $id)[0] ?? null;
  $host = Host::find($id);

  if (!$host) {
    Logger::error("No host found with ID: $id");
    return;
  }

  if (isset($_POST["action"])) {
    $action = $_POST["action"];
    switch ($action) {
      case "delete":
        $host->deleteVhost(true);

        if (Host::delete($id)) {
          Logger::storeLog("Deleted host with ID: $id");
          header("Location: /hosting");
        }
        else {
          Logger::error("Failed to delete host with ID: $id");
        }
        break;

      case "cancel":
        header("Location: /hosting");
        break;

      default:
        break;
    }
  }
  ?>

  <div>
    <form method="post">
      <?php Logger::log(
        "Are you sure you want to delete this host?",
        "Delete host " . $host->hostname
      ) ?>
      <button type="submit" name="action" value="delete" class="btn-danger">Delete</button>
      <button type="submit" name="action" value="cancel" class="btn-standard">Cancel</button>
    </form>
  </div>
  <?php
}