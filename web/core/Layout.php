<?php
namespace OpenPanel\core;
use OpenPanel\core\files\FileSystem;
use OpenPanel\core\logging\Logger;
// This is the layout file for the site. It contains the header, footer, and navigation bar.

class Layout {
  public static Meta $meta;
  
  /**
   * Layout constructs the HTML layout for the site.
   * @param callable $pagePath The path to the page to be included in the layout.
   */ 
  static function renderMain($pagePath)
  {
    $pageNotFound = false;
    $page = $pagePath;
    $page = file_exists($page . ".php") ? $page . ".php" : $page . "/index.php";

    // Navigation bar sections
    $navSections = [
      "Home" => ["/", __DIR__ . "/icons/walk.svg"],
      "Hosting" => ["/hosting", __DIR__ . "/icons/server-2.svg"],
      "Database" => ["/database", __DIR__ . "/icons/database.svg"],
      "Extensions" => ["/extend", __DIR__ . "/icons/puzzle.svg"],
    ];
    
    if (file_exists($page)) {
      require($page);

      
    } else {
      $pageNotFound = true;
    }

    // Meta variables
    $title = self::$meta->title ?? "Openpanel - Hosting Control Panel";

    // Setup extra navigation sections
    if (isset(self::$meta->nav)) {
      $navSecs = self::$meta->nav;

      foreach ($navSecs as $section => $sectionData) {
        $path = $sectionData[0];
        $icon = $sectionData[1] ?? null;
        $navSections[$section] = [$path, $icon];
      }
      
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="/core/styles/tailwind.min.css">
      <link rel="stylesheet" href="/core/styles/main.css">
      <title><?= $title ?></title>

      <?php
      if (function_exists("head")) {
        "head"();
      }
      ?>
      
    </head>

    <body>
      <?php
      if (self::$meta->navbarEnabled ?? true):
      ?>
      <div>
        <nav class="navpanel">
          <ul>
            <?php
            foreach ($navSections as $section => $sectionData) {
              $path = $sectionData[0];
              $icon = $sectionData[1] ?? null;
              echo "<a href=\"$path\"><li>";
              if (isset($icon)) echo FileSystem::import($icon);
              echo "<h3>$section</h3>";
              echo "</li></a>";
            }
            ?>
          </ul>
        </nav>
      </div>
      <?php
      endif;
      
      if (function_exists("page_before")) {
        "page_before"();
      }

      $useMargin = self::$meta->margin ?? true;
      ?>
      <main class="page-content <?= $useMargin ? "page-content-margin" : "" ?>">
        <?php
        if ($pageNotFound) {
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
        "page_after"();
      }
      ?>
    </body>

    </html>
    <?php
  }
}

Layout::$meta = new Meta();