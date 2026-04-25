
<!DOCTYPE html>

<?php
//$cookie_name = "user";
//# $cookie_value = "John Doe";
//$mapCenter = array("latCenter"=>61.24, "lonCenter"=>24.90);
//$squadratinhos = array("squadratinhosLineWeight"=>5, "squadratinhosColor"=>"#853A3A");
# https://www.w3schools.com/php/php_cookies.asp
# https://stackoverflow.com/questions/32567709/how-to-store-raw-json-string-in-cookie-with-php
//setcookie("MissingSquadrats", json_encode($mapCenter), time() + (86400 * 30)); // 86400 = 1 day
?>

<html>
<head>

</head>
<body>

<p>
<h1>Cookie test</h1>

<?php
$zoomLevel = 14;
$cookie_name = "MissingSquadrats";
$a = json_decode($_COOKIE[$cookie_name]);
// echo "Value is: " . $_COOKIE[$cookie_name];

var_dump(property_exists($a, "squadratsColor"));
if (property_exists($a, "squadratsColor")) {
  $squadratsColor = $a->squadratsColor;
  $squadratsLineWeight = $a->squadratsLineWeight;
} else {
  $squadratsColor = "#853A3A";
  $squadratsLineWeight = 2;
}
echo "$squadratsColor";

echo "<table border=\"1\">";
foreach ($a as $x => $y) {
  echo "<tr><td>$x</td><td>$y</td>";
}
echo "</table>";
if (isset($_COOKIE[$cookie_name])) {
  if ($zoomLevel == 14) {
    if (property_exists($a, "squadratinhosColor")) {
      $squadratinhosColor = $a->squadratinhosColor;
      $squadratinhosLineWeight = $a->squadratinhosLineWeight;
    } else {
      $squadratinhosColor = "#853A3A";
      $squadratinhosLineWeight = 2;
    }
  }
}

//  if (typeof data.squadratinhosColor === 'undefined') {
//    var squadratinhosColor = "#853A3A";
//  }
//  else {
//    var squadratinhosColor = data.squadratinhosColor;
//    var lineColor = squadratinhosColor;
//  }
?>

<script>

// https://stackoverflow.com/questions/5968196/how-do-i-check-if-a-cookie-exists
function getCookie(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else
    {
        begin += 2;
        var end = document.cookie.indexOf(";", begin);
        if (end == -1) {
        end = dc.length;
        }
    }
    // because unescape has been deprecated, replaced with decodeURI
    //return unescape(dc.substring(begin + prefix.length, end));
    return decodeURI(dc.substring(begin + prefix.length, end));
}

// https://www.geeksforgeeks.org/how-to-get-cookie-by-name-in-javascript/
// https://developer.mozilla.org/en-US/docs/Web/API/Document/cookie
function getCookieByName(name) {
  var rc = decodeURIComponent(document.cookie); //rc == result cookie
  const cookies = rc.split(";");
  for (let cookie of cookies) {
    cookie = cookie.trim();
    if (cookie.startsWith(name + "=")) {
      return cookie.substring(name.length + 1);
    }
  }
  return null;
}

if (getCookie("MissingSquadrats") == null) {
	var latCenter = 60.24;
	var lonCenter = 24.90;
  var squadratinhosColor = "#853A3A";
  var squadratinhosLineWeight = 5;
	alert("ok");
}
else {
	var data = JSON.parse(getCookieByName("MissingSquadrats"));
	var latCenter = data.latCenter;
	var lonCenter = data.lonCenter;
  var squadratinhosColor = data.squadratinhosColor;
  var squadratinhosLineWeight = data.squadratinhosLineWeight;
	alert(lonCenter);
}

</script>

</body>
</html>
