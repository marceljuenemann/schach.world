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
    "/bezirksliga/" => "http://nsv-online.de/ligen/bezirk1/bezirksliga/",
    "/bezirksklasse/" => "http://nsv-online.de/ligen/bezirk1/bezirksklasse/",
    "/kreisliga-west/" => "http://nsv-online.de/ligen/bezirk1/kreisliga-west/",
    "/kreisliga-ost/" => "http://nsv-online.de/ligen/bezirk1/kreisliga-ost/",
    "/kreisklasse-west/" => "http://nsv-online.de/ligen/bezirk1/kreisklasse-west/",
    "/kreisklasse-ost/" => "http://nsv-online.de/ligen/bezirk1/kreisklasse-ost/",
    "/omm/" => "http://nsv-online.de/ligen/bezirk1/omm/",
    "/hohlfeld/" => "https://nsv-online.de/ligen/bezirk1/hohlfeld-pokal/",
    "/pinnel-willeke/" => "https://nsv-online.de/ligen/bezirk1/pinnel-willeke-pokal/",
  
    "/u12/" => "http://nsv-online.de/ligen/sjbh/bmm-u12/",
    "/u14/" => "http://nsv-online.de/ligen/sjbh/bmm-u14/",
    "/u16/" => "http://nsv-online.de/ligen/sjbh/bmm-u16/",
    "/u20/" => "http://nsv-online.de/ligen/sjbh/bmm-u20/",

    "/impressum.php" => "/impressum",
    "/kontakt/" => "/vorstand",
    "/kontakt.php" => "/vorstand",
    "/vorstand.php" => "/vorstand",
    "/bezirk/kontakt" => "/vorstand",
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
