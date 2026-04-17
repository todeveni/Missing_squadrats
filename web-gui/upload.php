<!DOCTYPE html>

<?php
# https://www.w3schools.com/PHP/php_file_upload.asp

# echo "Overwrite:" . $_POST['overwrite'] . "<BR>\r\n";

libxml_use_internal_errors(true);

$tmpName = $_FILES['fileToUpload']['tmp_name'];

$dom = new \DOMDocument();

if (empty($tmpName) || !$dom->load($tmpName)) {
  die("Invalid .kml file");
}
$userName = clean($_POST['name']);
$NWlon = (float) $_POST['NWlon'] ?? 0;
$NWlat = (float) $_POST['NWlat'] ?? 0;
$SElon = (float) $_POST['SElon'] ?? 0;
$SElat = (float) $_POST['SElat'] ?? 0;
$squadratinhosLineWeight = (float) $_POST['squadratinhosLineWeight'] ?? 0;
$squadratinhosColor = str_replace("#","",$_POST['squadratinhosColor']);
$saveCookie = !empty($_POST['cookie']);
$target_dir = "../../jobs/missing_squadrats/";
$fileName = date('Y-m-d') . '-' . $userName;

if (!$NWlon || !$NWlat || !$SElon || !$SElat) {
  die('Invalid coordinates.');
}
# echo "Name: " . $userName . "<BR>\r\n";

if ($saveCookie) {
  # $mapCenter = array("latCenter"=>61.24, "lonCenter"=>24.90);
  $missinSquadrats = array("mapCenterLat"=>$SElat + (($NWlat - $SElat) / 2),
  "mapCenterLon"=>$SElon + (($NWlon - $SElon) / 2),
  "squadratinhosLineWeight"=>$squadratinhosLineWeight,
  "squadratinhosColor"=>"#" . $squadratinhosColor);
  # $squadratinhos = array("squadratinhosLineWeight"=>$squadratinhosLineWeight, "squadratinhosColor"=>$squadratinhosColor);
  # https://www.w3schools.com/php/php_cookies.asp
  # https://stackoverflow.com/questions/32567709/how-to-store-raw-json-string-in-cookie-with-php
  setcookie("MissingSquadrats", json_encode($missinSquadrats), time() + (86400 * 30)); // 86400 = 1 day
}

# https://stackoverflow.com/questions/14114411/remove-characters-that-arent-letters-and-numbers-replace-space-with-a-hyphen
function clean($string) {
   $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9]/', '', $string); // Removes special chars.
}
?>

<html>
<head>
  <meta charset="utf-8">
</head>
<body>

<h1>Upload a kml file</h1>

<p>Everything ok. Please go back to the main page</p><br>

<?php

#echo "OSM file name: " . $target_file . "<BR>\r\n";
if (move_uploaded_file($tmpName, $target_dir . $fileName . '.kml')) {
	echo "The file ". $fileName . " has been uploaded.<BR>\r\n";
} else {
		echo "Sorry, there was an error uploading your file.<BR>\r\n";
    exit;
}

$job = implode(',', [
  'filename' => $fileName,
  'username' => $userName,
  'nwlon' => $NWlon,
  'nwlat' => $NWlat,
  'selon' => $SElon,
  'selat' => $SElat,
  'squadratinhosLineWeight' => $squadratinhosLineWeight,
  'squadratinhosColor' => $squadratinhosColor,
]);
file_put_contents($target_dir . $fileName . '.csv', $job);

// In Docker: spawn Python directly so cron is not needed for local development.
if (file_exists('/.dockerenv')) {
  echo "Processing map, please wait...<BR>\r\n";
  flush();

  set_time_limit(0);

  $args = [
    '/usr/bin/python3',
    '/srv/src/missing_squadrats/missing_squadrats.py',
    $fileName . '.kml',
    $userName,
    (string) $NWlon,
    (string) $NWlat,
    (string) $SElon,
    (string) $SElat,
    (string) $squadratinhosLineWeight,
    $squadratinhosColor,
  ];
  $descriptors = [
    0 => ['pipe', 'r'],
    1 => ['file', '/home/users/oranta/missingSquadrats.log', 'a'],
    2 => ['file', '/home/users/oranta/missingSquadrats.log', 'a'],
  ];
  $proc = proc_open($args, $descriptors, $pipes, '/srv/src/missing_squadrats');
  if (is_resource($proc)) {
    fclose($pipes[0]);
    $exitCode = proc_close($proc);
    if ($exitCode === 0) {
      echo "Done! <a href=\"index.php\">Go back</a> to download your map.<br>\r\n";
    } else {
      echo "Processing failed (exit code $exitCode). Check the log for details.<br>\r\n";
    }
  } else {
    echo "Failed to start processing.<br>\r\n";
  }
}
?>

</body>
</html>
