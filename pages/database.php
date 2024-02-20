<?php
try {
  require_once(__DIR__ . "/../core/sql.php");
  require_once(__DIR__ . "/../core/logging/logger.php");

  $hosts = $sql->query("SELECT * FROM hosts");
} catch (\Throwable $th) {
  $hosts = [];
  Logger::error($th->getMessage());
}
?>

<div>
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
            <?= $host["portssl"] ?>
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