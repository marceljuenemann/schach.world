<?
  /////////////////////////////////////////////////
  //
  // PARAMETER:
  //     $_GET [location]=
  //     lat0-lat1-parameter1;lon2-lat2-parameter2;...;latN-lonN-parameterN
  //
  // AUTOR:
  //     Marcel Juenemann <mail@marcel-juenemann.de>
  //
  // BEARBEITET AM:
  //     2007-03-29
  //
  // LIZENZ:
  //     Do what you want!
  //
  /////////////////////////////////////////////////

  // Benutzerdefinierte Funktion zur Ortsmarkierung hier
  // Der Parameter kann z.B. den Ortsnamen enthalten
  function DrawLocation ( $img, $x, $y, $parameter )
  {
    // Blauen Kreis zeichnen
    $color = ImageColorAllocate ( $img, 0, 0, 255 );
    imagefilledellipse ( $img, (int) $x, (int) $y, 7, 7, $color );
  }

  // Berechnung der Koordinaten
  function CoordX ( $lon, $lat ) { return ( $lon - 5.9 ) * ( ( $lat - 47.10 ) * -0.3 + 42.50 ); }
  function CoordY ( $lat ) { return 513 - ( $lat - 47.10 ) * 63.50; }

  // Karte laden
  $img = imagecreatefrompng ( "MelliMap.png" );

  // Orte ausgeben
  foreach ( explode ( ";", $_GET ['locations'] ) as $location )
  {
    $data = explode ( "-", $location );
    if ( count ( $data ) >= 2 )
      DrawLocation ( $img, CoordX ( $data [1], $data [0] ), CoordY ( $data [0] ), $data [2] );
  }

  // Ausgeben
  imagepng ( $img );
  exit;
?>
