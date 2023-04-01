<?php
// TODO: Actually use PHPUnit test frameowrk...

compare('sjbh-2122', 1592);
compare('sjbh-2122', 1595);


function compare($liga, $staffel) {
  $expected = file_get_contents("$staffel.swi");
  $url = "https://nsv-online.de/ligen/$liga/?staffel=$staffel&m=export&format=swi";
  $actual = file_get_contents($url);
  if ($expected === $actual) {
    echo "Pass $staffel<br>";
  } else {
    echo "Failed $staffel<br>";
  }
  
}

