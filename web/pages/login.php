<?php
use OpenPanel\core\auth\User;
use OpenPanel\core\Layout;
use OpenPanel\core\webhost\Host;

function head() {
  ?>
  <title>OpenPanel - Login</title>
  <?php
}

Layout::$meta->margin = false;

function page() {
  if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];
    if (User::login($username, $password)) {
      header("Location: /");
      exit;
    }
  }
  
  ?>
  <div style="height: 100vh; display: flex; justify-content: center; align-items: center;">
    <div style="text-align: center; background-color: #f0f0f0; padding: 20px; border-radius: 10px;">
      <h1>
        Login
      </h1>

      <form action="/login" method="POST">
        <div style="display: flex; flex-direction: column; align-items: center;">
          <input type="text" name="username" placeholder="Username">
          <input type="password" name="password" placeholder="Password">
          <button type="submit">Login</button>
        </div>
      </form>
    </div>
  </div>
  <?php
}
?>