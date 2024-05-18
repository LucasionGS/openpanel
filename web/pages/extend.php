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
    else if ($_POST["action"] === "upgrade") {
      Extension::upgrade($_POST["extensionName"]);

      header("Location: /extend");
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
            <th></th>
            <th>Name</th>
            <th>Description</th>
            <th>Author</th>
            <th>Version (Disk)</th>
            <th>Version (Installed)</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $exts = Extension::all();

          foreach ($exts as $ext) {
            ?>
            <tr>
              <td style="position: relative">
                <div style="
                  position: absolute;
                  top: 50%;
                  right: 50%;
                  transform: translate(50%, -50%);
                  width: 100%;
                  aspect-ratio: 1/1;
                  border-radius: 50%;
                  background-color: <?= $ext->enabled ? "lightgreen" : "darkred" ?>;
                "></div>
                <?php if ($ext->version !== $ext->installed_version): ?>
                  <div style="
                    position: absolute;
                    top: 50%;
                    right: 50%;
                    transform: translate(50%, -50%);
                    width: 50%;
                    aspect-ratio: 1/1;
                    border-radius: 50%;
                    background-color: yellow;
                  "></div>
                <?php endif; ?>
              </td>
              <td>
              <?php if ($ext->enabled): ?>
                <a href="/_/<?= $ext->name ?>"><?= $ext->display_name ?? $ext->name ?></a>
              <?php else: ?>
                <?= $ext->display_name ?? $ext->name ?>
              <?php endif; ?>
              </td>
              <td><?= $ext->description ?></td>
              <td><?= $ext->author ?></td>
              <td><?= $ext->version ?></td>
              <td><?= $ext->installed_version ?></td>
              <!--<td><?= $ext->enabled ? "Yes" : "No" ?></td>-->
              <td>
                <form action="/extend" method="post">
                  <input type="hidden" name="extensionName" value="<?= $ext->name ?>">
                  <button <?= $ext->enabled ? "disabled": "" ?> type="submit" name="action" value="enable">Enable</button>
                  <button <?= !$ext->enabled ? "disabled": "" ?> type="submit" name="action" value="disable">Disable</button>
                  <?php if ($ext->version !== $ext->installed_version): ?>
                    <button class="btn-warning" type="submit" name="action" value="upgrade">Upgrade</button>
                  <?php endif; ?>
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