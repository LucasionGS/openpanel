<?php
use OpenPanel\core\auth\User;
use OpenPanel\core\webhost\Host;

function head() {
  ?>
  <title>OpenPanel - Home</title>
  <?php
}

function page() {
  $currentUser = User::current();
  ?>
    <div>
      <h1>
        Welcome to OpenPanel, <?= $currentUser->username ?>!
      </h1>
      OpenPanel is an open source project that aims to provide a simple and easy to use web interface for webhosts and databases.
      <hr>
      <h2>Statistics</h2>
      <?php
        $hosts = Host::count();
        if ($hosts > 0) {
          ?>
          <p>
            You have <?= $hosts ?> host(s)
          </p>
          <?php
        } else {
          ?>
          <p>
            You don't have any hosts yet. <a href="/hosts">Add one</a>
          </p>
          <?php
        }

        $users = User::count();

        if ($users > 0) {
          ?>
          <p>
            You have <?= $users ?> user(s)
          </p>
          <?php
        } else {
          ?>
          <p>
            You don't have any users yet. <a href="/users">Add one</a>
          </p>
          <?php
        }
      ?>
    </div>
  <?php
}
?>