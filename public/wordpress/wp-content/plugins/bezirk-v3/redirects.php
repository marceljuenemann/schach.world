<?
$request = $_SERVER ["REQUEST_URI"];

if ( $request == "/anmeldung/" || $request == "/anmeldung" ){
  $jahr = substr(date("Y"), 2);
  $jahr .= $jahr + 1;
  header("Location: http://nsv-online.de/ligen/bezirk1-$jahr/anmeldung.html");
  exit;
}
if ( $request == "/sjbh-anmeldung/" || $request == "/sjbh-anmeldung" ){
  $jahr = substr(date("Y"), 2);
  $jahr .= $jahr + 1;
  header("Location: http://nsv-online.de/ligen/sjbh-$jahr/anmeldung.html");
  exit;
}

// ein paar fest eingebaute Weiterleitungen
$fixed = array (
    "/bmm.html" => "http://nsv-online.de/ligen/bezirk1/",
    "/bmm/" => "http://nsv-online.de/ligen/bezirk1/",
    "/bezirksliga/" => "http://nsv-online.de/ligen/bezirk1/?staffel=1736&r=",
    "/bezirksklasse/" => "http://nsv-online.de/ligen/bezirk1/?staffel=1737&r=",
    "/kreisliga-west/" => "http://nsv-online.de/ligen/bezirk1/?staffel=1739&r=",
    "/kreisliga-ost/" => "http://nsv-online.de/ligen/bezirk1/?staffel=1738&r=",
    "/kreisklasse-west/" => "http://nsv-online.de/ligen/bezirk1/?staffel=1743&r=",
    "/kreisklasse-ost/" => "http://nsv-online.de/ligen/bezirk1/?staffel=1779&r=",
    "/omm/" => "http://nsv-online.de/ligen/bezirk1/?staffel=1740&r=",
    "/hohlfeld/" => "https://nsv-online.de/ligen/bezirk1/?staffel=1741&r=",
    "/pinnel-willeke/" => "https://nsv-online.de/ligen/bezirk1/?staffel=1742&r=",
  
    "/u12/" => "http://nsv-online.de/ligen/sjbh-2324/?staffel=1745&r=",
    "/u14/" => "http://nsv-online.de/ligen/sjbh-2324/?staffel=1746&r=",
    "/u16/" => "http://nsv-online.de/ligen/sjbh-2324/?staffel=1748&r=",
    "/u20/" => "http://nsv-online.de/ligen/sjbh-2324/?staffel=1747&r=",
    "/u20-liga/" => "http://sjbh.de/u20/",
    "/u20-klasse/" => "http://sjbh.de/u20/",

    "/impressum.php" => "/bezirk/impressum",
    "/kontakt.php" => "/bezirk/impressum/#kontakt",
    "/vorstand.php" => "/bezirk/vorstand",
    "/bezirk/kontakt" => "/bezirk/impressum/#kontakt",
    );
if ( isset ( $fixed [$request] ) ){
    header("Location: ".$fixed[$request]);
    exit;
}
if ( isset ( $fixed [$request."/"] ) ){
    header("Location: ".$fixed[$request."/"]);
    exit;
}

// Support old links like https://sjbh.de/downloads/Logowettbewerb.pdf
if ( ( $download = strstr ( $request, "/downloads/" ) ) !== false ){
    $request = $download;
}
if ( $request && $request != '/' && $request != '//' && file_exists(ABSPATH . "../downloads/archiv".$request ) ){
    header("Location: https://schachbezirk-hannover.de/downloads/archiv".$request );
    exit;
}
