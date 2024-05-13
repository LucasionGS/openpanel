<?php
use OpenPanel\core\logging\Logger;

function head()
{
  ?>
  <title>OpenPanel - Hosting</title>
  <?php
}

function page()
{
  $sql = \OpenPanel\core\db\Database::getInstance();
  try {
    $hosts = $sql->query("SELECT * FROM hosts");
  } catch (\Throwable $th) {
    $hosts = [];
    Logger::error($th->getMessage());
  }
  ?>

  <div>
    <a href="/hosting/edit">
      <button type="submit">
        Create new host
      </button>
    </a>

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
              <a href="/hosting/edit?id=<?= $host["id"] ?>">
                <button type="submit" name="action" value="edit">
                  Edit
                </button>
              </a>
              <a href="/hosting/delete?id=<?= $host["id"] ?>">
                <button type="submit" name="action" value="delete" class="btn-danger">
                  Delete
                </button>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php
}