<?php
use OpenPanel\core\auth\User;
use OpenPanel\core\Extension;
use OpenPanel\core\webhost\Host;

function head() {
  ?>
  <title>OpenPanel - Extensions</title>
  <?php
}

function page() {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($_POST["action"] === "refresh") {
      Extension::refresh();
      $exts = Extension::all();
      $extsFiles = Extension::getExtensionListFromDisk();
      foreach ($exts as $ext) {
        if (!in_array($ext->name, $extsFiles)) {
          Extension::uninstall($ext->name);
        }
      }
    }
    else if ($_POST["action"] === "enable") {
      Extension::enable($_POST["extensionName"]);
    }
    else if ($_POST["action"] === "disable") {
      Extension::disable($_POST["extensionName"]);
    }
  }
  ?>
    <div>
      <h1>
        Extensions
      </h1>
      <form action="/extend" method="post">
        <button type="submit" name="action" value="refresh">Refresh extensions</button>
      </form>
      <table>
        <thead>
          <tr>
            <th>Name</th>
            <th>Enabled</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $exts = Extension::all();

          foreach ($exts as $ext) {
            ?>
            <tr>
              <?php
              if ($ext->enabled):
              ?>
              <td>
                <a href="/_/<?= $ext->name ?>"><?= $ext->name ?></a>
              </td>
              <td>Yes</td>
              <?php
              else:
              ?>
              <td>
                <?= $ext->name ?>
              </td>
              <td>No</td>
              <?php
              endif;
              ?>
              <td>
                <!--
                <a href="/extensions/<?= $ext->id ?>/enable">Enable</a>
                <a href="/extensions/<?= $ext->id ?>/disable">Disable</a>
                -->
                <form action="/extend" method="post">
                  <input type="hidden" name="extensionName" value="<?= $ext->name ?>">
                  <button <?= $ext->enabled ? "disabled": "" ?> type="submit" name="action" value="enable">Enable</button>
                  <button <?= !$ext->enabled ? "disabled": "" ?> type="submit" name="action" value="disable">Disable</button>
                </form>
              </td>
            </tr>
            <?php
          }
          ?>
        </tbody>
      </table>
    </div>
  <?php
}
?>