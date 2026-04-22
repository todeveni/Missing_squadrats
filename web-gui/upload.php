<!DOCTYPE html>

<?php

function clean($string) {
   $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.

   return preg_replace('/[^A-Za-z0-9]/', '', $string); // Removes special chars.
}

function validate_squadrats_kml($file): bool {
  libxml_use_internal_errors(true);

  $dom = new \DOMDocument();
  if (!$dom->load($file)) {
    return FALSE;
  }

  // Search for Kml->Document->Placemark elements.
  $placemarks = $dom->getElementsByTagName('kml')->item(0)
    ?->getElementsByTagName('Document')->item(0)
    ?->getElementsByTagName('Placemark');

  if (!$placemarks->length > 0) {
    return FALSE;
  }

  // Search for Placemark->name element with the value of "squadrats".
  foreach ($placemarks as $placemark) {
    $name = $placemark->getElementsByTagName('name')->item(0)?->textContent;

    if ($name === 'squadrats') {
      return TRUE;
    }
  }
  return FALSE;
}


$tmpName = $_FILES['fileToUpload']['tmp_name'];

if (empty($tmpName) || !validate_squadrats_kml($tmpName)) {
  die("Invalid .kml file.");
}

$userName = clean($_POST['name']);
$NWlon = (float) $_POST['NWlon'] ?? 0;
$NWlat = (float) $_POST['NWlat'] ?? 0;
$SElon = (float) $_POST['SElon'] ?? 0;
$SElat = (float) $_POST['SElat'] ?? 0;
$squadratinhosLineWeight = (float) $_POST['squadratinhosLineWeight'] ?? 0;
$squadratinhosColor = str_replace("#", "", clean($_POST['squadratinhosColor']));
$saveCookie = !empty($_POST['cookie']);
$target_dir = "../../jobs/missing_squadrats/";
$fileName = date('Y-m-d') . '-' . $userName;

if (!$NWlon || !$NWlat || !$SElon || !$SElat) {
  die('Invalid coordinates.');
}
# echo "Name: " . $userName . "<BR>\r\n";

if ($saveCookie) {
  # $mapCenter = array("latCenter"=>61.24, "lonCenter"=>24.90);
  $missinSquadrats = array(
    "mapCenterLat" => $SElat + (($NWlat - $SElat) / 2),
    "mapCenterLon" => $SElon + (($NWlon - $SElon) / 2),
    "squadratinhosLineWeight" => $squadratinhosLineWeight,
    "squadratinhosColor" => "#" . $squadratinhosColor,
  );
  # $squadratinhos = array("squadratinhosLineWeight"=>$squadratinhosLineWeight, "squadratinhosColor"=>$squadratinhosColor);
  # https://www.w3schools.com/php/php_cookies.asp
  # https://stackoverflow.com/questions/32567709/how-to-store-raw-json-string-in-cookie-with-php
  setcookie("MissingSquadrats", json_encode($missinSquadrats), time() + (86400 * 30)); // 86400 = 1 day
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
?>

</body>
</html>
