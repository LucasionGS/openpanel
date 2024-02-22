<?php
function importSvg($path) {
  $svg = file_get_contents($path);
  return $svg;
}