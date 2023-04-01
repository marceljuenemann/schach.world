<?php
/**
 * Format für Bezirk 2
 * ===================
 * Alle Spieler werden in einer einzigen Rangliste mit Nummern von 1 bis N geführt. Die Rangliste
 * enthält des Weiteren auch Spieler aus den Landes- und Verbandsligen.
 */

  $style = "style='font-family: Verdana; font-size:12pt; border:solid; border-color:black; border-collapse:collapse; border-width:1px'";
  $r = $prefs['runden'];

  // Verein holen
  $rsrc = mysql_query ( "SELECT ZPS zps, Vereinname name FROM dwz_vereine WHERE ZPS like '$_GET[zps]%' ORDER BY Vereinname", $globals ["db"] );
  while ( $verein = ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) ){
    // Headline.
    echo "<h1 style='font-family:Verdana;font-size:16pt'>$verein[name]</h1>";

    // Table header.
    echo "<table  cellspacing='0' cellpadding='3' style='font-family: Verdana; font-size:12pt; border-collapse: collapse '>";
    echo "<tr $style><th $style>Nr.</th><th $style>Name, Vorname</th><th $style>GJ</th><th $style>DWZ</th>";
    for ( $i = 1; $i <= $r; ++$i )
        echo "<th $style>$i</th>";
    echo "<th $style>ges</th></tr>";

    // Players.
    $rsrc2 = mysql_query ( "SELECT s.titel, s.nachname, s.vorname, s.brettnr, s.dwz, s.geburt, m.mnr, m.staffel
        FROM spieler s INNER JOIN mannschaften m ON m.id=s.mannschaft
        WHERE m.turnier='$globals[tid]' AND m.zps='$verein[zps]'
        ORDER BY m.mnr, s.brettnr", $globals ["db"] );
    for ($nr = 1; $spieler = mysql_fetch_array ( $rsrc2, MYSQL_ASSOC ); $nr++){
      $nachname = strlen ( $spieler ["titel"] )?"$spieler[titel] $spieler[nachname]":$spieler["nachname"];
      $dwz = $spieler ["dwz"] ? $spieler ["dwz"] : " ";

      echo "<tr $style>";
      echo "<td $style>$nr</td>";
      echo "<td $style>$nachname, $spieler[vorname]</td>";
      echo "<td $style align='right'>$spieler[geburt]</td>";
      echo "<td $style align='right'>$spieler[dwz]</td>";
      for ( $i = 1; $i <= $r + 1; ++$i )
          echo "<td $style>&nbsp;</td>";
      echo "</tr>";
    }

    echo "</table>";
    echo "<br /><br />";
  }
