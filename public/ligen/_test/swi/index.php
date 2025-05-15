<?php
// TODO: Actually use PHPUnit test frameowrk...
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

compare('sjbh-2122', 'bmm-u12', 1592);
compare('sjbh-2122', 'bmm-u20', 1595);
compare('nsj-2324', 'landesklasse-west', 1780);


function compare($liga, $staffel, $id) {
  $expected = file_get_contents("$id.swi");
  $url = "https://nsv-online.de/ligen/$liga/$staffel/swi/";
  $actual = file_get_contents($url);
  if ($expected === $actual) {
    echo "Pass $staffel<br>";
  } else {
    echo "Failed $staffel<br>";
    echo "Expected: <pre>$expected</pre>";    
    echo "Actual: <pre>$actual</pre>";    
  }
  
}

