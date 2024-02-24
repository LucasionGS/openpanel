<?php
// This is the layout file for the site. It contains the header, footer, and navigation bar.

/**
 * Layout constructs the HTML layout for the site.
 * @param callable $pagePath The path to the page to be included in the layout.
 */ 
function layout($pagePath)
{
  $pageNotFound = false;
  $page = $pagePath;
  $page = file_exists($page . ".php") ? $page . ".php" : $page . "/index.php";
  if (file_exists($page)) {
    require($page);

    // Meta options
    // This is a way to pass options to the layout from the page
    $meta = $meta ?? [];
    
  } else {
    $pageNotFound = true;
  }
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/core/styles/tailwind.min.css">
    <link rel="stylesheet" href="/core/styles/main.css">

    <?php
    if (function_exists("head")) {
      head();
    }
    ?>
    
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
    <?php
    if (function_exists("page_before")) {
      page_before();
    }

    $useMargin = $meta["margin"] ?? true;
    ?>
    <main class="page-content <?= $useMargin ? "page-content-margin" : "" ?>">
      <?php
      if ($pageNotFound) {
        require(__DIR__ . "/logging/logger.php");
        Logger::error("Page not found: $page");
        http_response_code(404);
      }
      if (function_exists("page")) {
        page();
      }
      ?>
    </main>
    <?php
    if (function_exists("page_after")) {
      page_after();
    }
    ?>
  </body>

  </html>
  <?php
?>
<?php
}

?>