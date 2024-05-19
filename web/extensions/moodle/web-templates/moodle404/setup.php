<?php
use OpenPanel\core\webhost\Host;

const MOODLE_DOWNLOAD = "https://packaging.moodle.org/stable404/moodle-latest-404.zip";

/**
 * This setup function will download and install Moodle, and change $addons accordingly
 */
function setup(Host $host, array &$addons) {
  $www = $host->getWebRoot();
  // Download Moodle
  $moodleZip = "/tmp/moodle.zip";
  file_put_contents($moodleZip, fopen(MOODLE_DOWNLOAD, 'r'));
  // $ch = curl_init(MOODLE_DOWNLOAD);
  // $fp = fopen($moodleZip, "w");
  // curl_setopt($ch, CURLOPT_FILE, $fp);
  // curl_setopt($ch, CURLOPT_HEADER, 0);
  // curl_exec($ch);
  // curl_close($ch);
  // fclose($fp);

  // Unzip Moodle
  $zip = new ZipArchive;
  if ($zip->open($moodleZip) === TRUE) {
    $zip->extractTo($www);
    $zip->close();
  } else {
    echo "Failed to unzip Moodle";
  }

  // Rename moodle directory
  $moodleDir = glob("$www/moodle-*")[0];
  exec("mv \"$moodleDir\" moodle");

  // Create moodledata directory
  $moodleData = "$www/moodledata";
  mkdir($moodleData, 0777, true);
  // Change $addons
  $addons["root"] = $www . "/moodle";
  $addons["phpVersion"] = "8.1";
}