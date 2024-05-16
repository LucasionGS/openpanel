<?php
namespace OpenPanel\core;

/**
 * Information about the page that will be rendered.
 */
class Meta {
  public $margin = true;
  public $title = "OpenPanel - Hosting Control Panel";

  public bool $navbarEnabled = true;
  /**
   * Navigation sections.
   * Each section is an array with the first element being the path and the second element being the icon.
   * Format: $meta->$nav["Title"] = ["/path", "icon.svg"]
   * @var string[]
   */
  public array $nav = [];
}