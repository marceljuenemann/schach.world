<?php
// TODO: Actually use PHPUnit test frameowrk...
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

compare('sjbh-2122', 1592);
compare('sjbh-2122', 1595);
compare('nsj-2324', 1780);


function compare($liga, $staffel) {
  $expected = file_get_contents("$staffel.swi");
  $url = "https://nsv-online.de/ligen/$liga/?staffel=$staffel&m=export&format=swi";
  $actual = file_get_contents($url);
  if ($expected === $actual) {
    echo "Pass $staffel<br>";
  } else {
    echo "Failed $staffel<br>";
    echo "Expected: <pre>$expected</pre>";    
    echo "Actual: <pre>$actual</pre>";    
  }
  
}

