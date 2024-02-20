<?php
// This is the layout file for the site. It contains the header, footer, and navigation bar.
/**
 * Layout constructs the HTML layout for the site.
 * @param string|callable $content The content to display in the layout. This can be a string or a function that returns a string that will be required into this file.
 */
function layout($content)
{
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
      <?php echo "Test" ?>
    </title>
    <link rel="stylesheet" href="/core/styles/main.css">
  </head>

  <body>
    <div>
      <nav class="navpanel">
        <ul>
          <a href="/">
            <li>
              <?php echo importSvg(__DIR__ . "/icons/walk.svg") ?>
              <h3>Home</h3>
            </li>
          </a>
          <a href="/hosting">
            <li>
              <?php echo importSvg(__DIR__ . "/icons/server-2.svg") ?>
              <h3>Hosting</h3>
            </li>
          </a>
          <a href="/database">
            <li>
              <?php echo importSvg(__DIR__ . "/icons/database.svg") ?>
              <h3>Database</h3>
            </li>
          </a>
        </ul>
      </nav>
    </div>
    <main class="page-content">
      <?php
      if (is_callable($content)) {
        $page = $content();
        $page = (file_exists($page . ".php") ? $page . ".php" : $page . "/index.php");
        if (file_exists($page)) {
          require_once($page);
        } else {
          echo "Page not found";
        }
      } else {
        echo $content;
      }
      ?>
    </main>
  </body>

  </html>
  <?php
?>
<?php
}

?>