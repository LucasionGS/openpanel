<?php
use OpenPanel\core\db\Database;
use OpenPanel\core\webhost\Host;
function page() {
  ?>
    Test extension
    <hr>
  <?php
  $hosts = Host::all();
  foreach ($hosts as $host) {
    ?>
      <div>
        <h3><?= $host->hostname ?></h3>
        <p>Port: <?= $host->port ?></p>
        <p>Port SSL: <?= $host->portssl ?></p>
        <a href="<?= $host->getEditUrl() ?>">Edit</a>
        <a href="<?= $host->getDeleteUrl() ?>">Delete</a>
      </div>
    <?php
  }
}
?>