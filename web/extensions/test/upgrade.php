<?php
use OpenPanel\core\Settings;
function upgrade(int $oldVersion, int $newVersion) {
  if ($newVersion > $oldVersion) {
    Settings::set("test_extension_version", $newVersion);
  }
  else if ($newVersion < $oldVersion) {
    Settings::clear("test_extension_version");
  }
}