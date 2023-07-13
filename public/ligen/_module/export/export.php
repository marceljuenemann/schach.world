<?
/* Mannschaftsaufstellung und ähnliches
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage mannschaft
 */
    require_once ( "turnier.inc.php" );
    require_once ( "mannschaft.class.php" );

    function getZusatz ( $feld ){
        global $globals;
        $mid = $_GET ["mid"];
        return reset ( mysql_fetch_array ( mysql_query ( "SELECT inhalt FROM anmeldungZusatzfelder WHERE feldname LIKE '$feld' AND mannschaft=$mid", $globals ["db"] ), MYSQL_ASSOC ) );
    }

    function toInline ( $str ){
        return str_replace ( "\n", ", ", str_replace ( "\r\n", "\n", trim ( $str ) ) );
    }

    function replaceUmlauts ( $str ){
    return str_replace(
      array(utf8_decode('ä'), utf8_decode('ö'), utf8_decode('ü'), utf8_decode('ß')),
      array('ae', 'oe', 'ue', 'ss'),
      $str
    );
  }

if ( isset($_GET['zip']) ) {
  $tmpfile = tempnam(sys_get_temp_dir(), "sed_export_zip");
  $zip = new ZipArchive;
  $res = $zip->open($tmpfile, ZipArchive::CREATE);
  if ($res === TRUE) {
    foreach ($globals['staffeln'] as $id => $name) {
      $content = file_get_contents($globals['httppath'].$prefs['directory'].'/?m=export&format='.$_GET['format'].'&staffel='.$id);
      $zip->addFromString(replaceUmlauts($name).'.'.$_GET['format'], $content);
    }
    $zip->close();

      header("Content-Type: application/zip");
    header("Content-Disposition: attachment; filename=Export.zip");
    header("Content-Length: " . filesize($tmpfile));

    readfile($tmpfile);
    exit;
  } else {
    SED_Error ("Could not create zip file", true);
  }
}

if ( $_GET ["format"] == "mlf" ){

    if ( !isset ( $_GET ['staffel'] ) ){
        SED_Error ( "Keine Staffel-ID", true );
    }

    header ( "Content-type: text/plain" );

    // MLF-interne IDs
    $reg_mannschaften = array (); // mid => mlf-id
    $reg_spieler = array ( ""=>"" ); // sid => mlf-id
    $reg_spieler_iterator = 1; // Zum Zählen der ID

    // Mannschaften sammeln TODO: Alle Mannschaften, die in der Staffel gespielt haben
    $rsrc_mannschaften = mysql_query ( "select m.id from mannschaften as m where staffel=$_GET[staffel] order by m.name" );
    $count_mannschaften = mysql_num_rows ( $rsrc_mannschaften );

    // Mannschaften durchgehen
    for ( $i = 1; $entry_mannschaft = mysql_fetch_array ( $rsrc_mannschaften, MYSQL_ASSOC ); ++$i )
    {
        // Mannschaftsdaten laden
        $team = new SED_Mannschaft ( $entry_mannschaft["id"] );
        $einzelergebnisse = $team->getErgebnisse ();

        // Mannschaft registrieren
        $reg_mannschaften [$entry_mannschaft['id']] = $i;
        echo "R|$i|0|$i|".$team->get("mannschaftsname")."\r\n";

        // Spieler durchgehen
        $j = 1; // Mannschaftsinterne Spieler-Nummer
        foreach ( $team->getAufstellung () as $spieler ){
            // Wurde der Spieler gar nicht eingesetzt?
            if ( $spieler["istErsatz"] && ((int) @$einzelergebnisse[$spieler["id"]]["einsaetze"]) == 0 )
            { continue; }

            // ZPS zusammensetzen
            $tmp = explode ( "-", $spieler ["zps"] );
            $zps = @sprintf ( "%s%04s", $tmp [0], $tmp [1] );
            $zps = str_replace ( "0000", "****", $zps ); // unbekannt

            // Registrieren und ausgeben
            $reg_spieler [$spieler['id']] = $reg_spieler_iterator++;
            echo "R|$i|$j|".$reg_spieler [$spieler['id']]."|$spieler[nachname],$spieler[vorname]|$zps\r\n";
            ++$j;
        }
    }

  //D|MANNSCHAFTEN|22
  echo "D|MANNSCHAFTEN|$count_mannschaften\r\n";

  // Paarungen sammeln
  $rsrc_paar = mysql_query ( "select p.id, p.runde, p.mannschaft1, p.mannschaft2 from paarungen as p where p.staffel=$_GET[staffel] order by p.runde" );

  // Paarungen durchgehen
  for ( $i = 1; $entry_paar = mysql_fetch_array ( $rsrc_paar, MYSQL_ASSOC ); ++$i )
  {
    // Ausgeben
    echo "B|$i|0|".$reg_mannschaften [$entry_paar ['mannschaft1']]."|".$reg_mannschaften [$entry_paar ['mannschaft2']]."|$entry_paar[runde]\r\n";

    // Spieler-Paarungen suchen
    $rsrc_spaar = mysql_query ( $x = "SELECT * FROM spielerpaarungen WHERE paarung=$entry_paar[id] ORDER BY brett" );

    // Spielerpaarungen durchgehen
    for ( $j = 1; $entry_spaar = mysql_fetch_array ( $rsrc_spaar, MYSQL_ASSOC ); ++$j )
    {
      // Ausgeben
      if ( $entry_spaar ['ergebnis1'] == SED_REMIS ) $entry_spaar ['ergebnis1'] = "r";
      if ( $entry_spaar ['ergebnis2'] == SED_REMIS ) $entry_spaar ['ergebnis2'] = "r";
      echo "B|$i|$entry_spaar[brett]|".$reg_spieler [$entry_spaar ['spieler1']]."|".$reg_spieler [$entry_spaar ['spieler2']]."|$entry_spaar[ergebnis1]|$entry_spaar[ergebnis2]\r\n";
    }
  }

  echo "D|BRETTER|".SED_GetBrettzahl ($_GET ['staffel'])."\r\n";
  echo "D|RUNDEN|".SED_GetRundenzahl ($_GET ['staffel'])."\r\n";
  $staffelname = reset ( mysql_fetch_array ( mysql_query ( "select name from staffeln where id=$_GET[staffel]" ), MYSQL_ASSOC ) );
  echo "D|LIGANAME|$staffelname\r\n";

}
// END MLF
/////////////////////////////

else if ( $_GET ["format"] == "swi" ){
  require_once("../_module/export/swi.inc.php");
}

else if ( $_GET ["format"] == "u12spielplaner"){
  require_once("../_module/export/u12spielplaner.inc.php");
}

else if ( $_GET ["format"] == "tischschilder" ){
  require_once("../_module/export/tischschilder.inc.php");
}

else if ( $_GET ["format"] == "702" ){
  require_once("../_module/export/bezirk2.inc.php");
}

else if ( $_GET ["format"] == "peter" || $_GET["format"] == "701" ){

// BEGIN BEZ1
    header ( "Content-type: text/plain" );

    // Verein holen
    $rsrc = mysql_query ( "SELECT ZPS, Vereinname FROM dwz_vereine WHERE ZPS like '$_GET[zps]%' ORDER BY ZPS", $globals ["db"] );
    while ( $verein = ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) ){

        // Verein Überschrift
        $zps = substr ( $verein["ZPS"], 3, 2 );
        echo "\t\t$zps $verein[Vereinname]\n\n\n";

        // Spieler holen
        $rc = mysql_query ( "SELECT s.titel, s.nachname, s.vorname, s.brettnr, s.dwz, s.zps, m.mnr
            FROM spieler s INNER JOIN mannschaften m ON m.id=s.mannschaft
            WHERE m.turnier='$globals[tid]' AND m.zps='$verein[ZPS]'
            ORDER BY m.mnr, s.brettnr", $globals ["db"] );

        // Spieler iterieren
        $lastmnr = 1;
        $lastbnr = 0;
        while ( $spieler = mysql_fetch_array ( $rc, MYSQL_ASSOC ) ){
            // bis 12 Leerzeilen auffüllen
            if ( $spieler["mnr"]!=$lastmnr ){
                $lastbnr = (int) $lastbnr;
                while ( $lastbnr < 12 ){
                    $lastbnr ++;
                    $tmp = $lastbnr>9 ? $lastbnr : "0$lastbnr";
                    echo "$lastmnr\t$tmp\t \t \t \t \n";
                }
            }

            $name = strlen ( $spieler ["titel"] )?"$spieler[titel] $spieler[nachname]":$spieler["nachname"];
            $zps = explode ( "-", $spieler ["zps"] );
            $mglnr = isset($zps[1]) ? $zps[1] : "VS";
            $dwz = $spieler ["dwz"] ? $spieler ["dwz"] : " ";
            $brettnr = (strlen($spieler["brettnr"])==3 ? substr($spieler["brettnr"],1) : $spieler["brettnr"]);
      $brettnr = (strlen($brettnr)==1 ? "0$brettnr" : $brettnr);

            echo "$spieler[mnr]\t";
            echo $brettnr."\t";
            echo "$name\t$spieler[vorname]\t$mglnr\t$dwz\n";

            $lastmnr = $spieler ["mnr"];
            $lastbnr = $brettnr;
        }

        echo "\n\n\n===\n\n\n";
    }


} // END BEZ1

else if ( $_GET ["format"] == "701-so" ){

// BEGIN BEZ1-SO
    // Verein holen
    echo "<table>";
    $rsrc = mysql_query ( "SELECT ZPS, Vereinname FROM dwz_vereine WHERE ZPS like '701%' ORDER BY ZPS", $globals ["db"] );
    while ( $verein = ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) ){

        // Spiellokal suchen
        $rc = mysql_query ( "SELECT * FROM mannschaften WHERE turnier='$globals[tid]' AND zps='$verein[ZPS]' ORDER BY mnr", $globals ['db'] );
        $team = mysql_fetch_array ( $rc, MYSQL_ASSOC );

        $zps = substr ( $verein["ZPS"], 3, 2 );
        echo "<tr><td rowspan='2' style='vertical-align:top' valign='top'><b><u>$zps</u> $verein[Vereinname]</b></td><td>$team[so_name]</td><td>$team[so_strasse]</td></tr>";
        echo "<tr><td>$team[so_telefon]</td><td>$team[so_plz] $team[so_stadt]</td></tr>";

        // Mannschaften holen
        do {
            $_GET ["mid"] = $team["id"];
            echo "<tr><td>MF $team[mnr]. Mannschaft</td><td>$team[mf_name]</td><td>".getZusatz(utf8_encode("Mannschaftsf%hrer - Stra%e und Hausnummer"))."</td></tr>";
            echo "<tr><td>&nbsp;</td><td>$team[mf_telefon]</td><td>".getZusatz(("Mannschaftsf%hrer - PLZ"))." ".getZusatz(("Mannschaftsf%hrer - Ort"))."</td></tr>";
            echo "<tr><td>&nbsp;</td><td colspan='2'>eMail: $team[mf_email]</td></tr>";
        } while ( $team = mysql_fetch_array ( $rc, MYSQL_ASSOC ) );
    }


} // END BEZ1

else if ( $_GET["format"] == "spiellokale" ){

    header ( "Content-type: text/plain" );

    // Verein holen
    $rsrc = mysql_query ( "SELECT ZPS FROM dwz_vereine WHERE ZPS like '7%' ORDER BY ZPS", $globals ["db"] );
    while ( $verein = ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) ){
        // Spiellokal holen
        $rc = mysql_query ( "SELECT * FROM mannschaften WHERE zps='$verein[ZPS]' ORDER BY id DESC", $globals ["db"] );
        $so = ( mysql_fetch_array ( $rc, MYSQL_ASSOC ) );
        echo "$verein[ZPS]\t$so[so_name]\t$so[so_strasse]\t$so[so_stadt]\t$so[so_plz]\t$so[so_telefon]\n";
    }
}



elseif ($_GET["format"]=="sjbh") {
// BEGIN SJBH HEFT

// TODO nächstes jahr: gleich zwei leere zeilen, telefon+ort gleich in new line statt komma
    $style = "style='font-family: Verdana; font-size:12pt; border:solid; border-color:black; border-collapse:collapse; border-width:1px'";

    // Mannschaften holen. Sortierung Staffel, ZPS
    $second = false;
    $rsrc = mysql_query ( "SELECT m.id FROM mannschaften m INNER JOIN staffeln s ON s.id=m.staffel WHERE m.turnier=$globals[tid] ORDER BY s.sortid, m.staffel DESC, m.zps", $globals ["db"] );
  echo mysql_error();
    while ( $mid = @reset ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) ){

        // Objekt erstellen
        $_GET ['mid'] = $mid;
        $team = new SED_Mannschaft ( $mid );

        // DSB-Namen laden
        $rc = mysql_query ( "SELECT Vereinname FROM dwz_vereine WHERE ZPS='".$team->get("zps")."'", $globals ["db"] );
        $name = @reset ( mysql_fetch_array ( $rc, MYSQL_ASSOC ) );
        $nr = ($team->get("mnr") > 1 ? " ".$team->get("mnr") : "");
        $sname = str_replace ( "BMM ", "", $globals["staffeln"][$team->get("staffel")] );
        echo "<h1 style='font-family:Verdana;font-size:16pt'>$sname: $name $nr</h1>";

        // MF + SO
        echo "<table style='font-family: Verdana; font-size:12pt; width=100%'>";

        // MF
        echo "<tr><td style='vertical-align: top; font-family: Verdana; font-size:12pt; width:50%'><b>Mannschaftsf&uuml;hrer:</b><br />";
        echo $team->get("mf_name")."<br />";
        echo "Tel.: ".$team->get ( "mf_telefon" );
        if ( strlen ( $tel = $team->get ( "mf_telefon2" ) ) )
            echo " oder $tel";
        echo "<br />".$team->get ( "mf_email" )."</td><td style='vertical-align: top;  padding-left: 10px; font-family: Verdana; font-size:12pt; width:50%'>";

        // SO
        echo "<b>Spiellokal:</b><br />";
        echo $team->get("so_name")."<br />";
        echo $team->get("so_strasse").", ".$team->get("so_plz")." ".$team->get("so_stadt")."";
        if ( strlen ( $tel = $team->get ( "so_telefon" ) ) )
            echo "<br />Telefon: $tel";
    else
      echo "<br />&nbsp;";
        echo "</td></tr></table><br />";

        // Aufstellung
        echo "<table  cellspacing='0' cellpadding='3' style='font-family: Verdana; font-size:12pt; border-collapse: collapse '>";
        echo "<tr $style><th $style>Nr.</th><th $style>Name</th><th $style>DWZ</th>";
        $r = SED_GetRundenzahl ( $team->get ( "staffel" ) );
        for ( $i = 1; $i <= $r; ++$i )
            echo "<th $style>$i</th>";
        echo "<th $style>Pkt</th></tr>";
        $data = $team->getAufstellung ();
        foreach ( $data as $spieler )
        {
            if ( $spieler ["istErsatz"] ) continue;
            echo "<tr $style>";
            echo "<td $style>&nbsp;$spieler[brettnr]</td>";
            echo "<td $style>".SED_Spielername($spieler)."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            echo "<td $style align='right'>$spieler[dwz]</td>";
            for ( $i = 1; $i <= $r + 1; ++$i )
                echo "<td $style>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<br /><br />";
    }

}// END SJBH









elseif ($_GET["format"]=="nsv") {
// BEGIN SJBH HEFT

    $style = "style='font-family: Verdana; font-size:12pt; border:solid; border-color:black; border-collapse:collapse; border-width:1px'";

    // Mannschaften holen. Sortierung Staffel, ZPS
    $second = false;
    $rsrc = mysql_query ( "SELECT id FROM mannschaften m WHERE m.turnier=$globals[tid] AND m.staffel=$_GET[staffel] ORDER BY m.staffel DESC, m.zps, m.mnr", $globals ["db"] );
    while ( $mid = @reset ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) ){

        // Objekt erstellen
        $_GET ['mid'] = $mid;
        $team = new SED_Mannschaft ( $mid );

        // DSB-Namen laden
        $rc = mysql_query ( "SELECT Vereinname FROM dwz_vereine WHERE ZPS='".$team->get("zps")."'", $globals ["db"] );
        $name = @reset ( mysql_fetch_array ( $rc, MYSQL_ASSOC ) );
        $nr = ($team->get("mnr") > 1 ? " ".$team->get("mnr") : "");
        $sname = str_replace ( "BMM ", "", $globals["staffeln"][$team->get("staffel")] );
        echo "<h1 style='font-family:Verdana;font-size:16pt'>$name $nr</h1>";

        // MF + SO
        echo "<table style='font-family: Verdana; font-size:12pt; width=100%'>";



        // MF
        echo "<tr><td style='vertical-align: top; font-family: Verdana; font-size:12pt; width:50%'><b>Mannschaftsf&uuml;hrer:</b><br />";
        echo $team->get("mf_name")."<br />";
        echo toInline ( getZusatz ( utf8_decode( "Mannschaftsführer - Adresse" )))."<br>";
        echo toInline ( getZusatz ( utf8_decode( "Mannschaftsführer - PLZ" )))." ".getZusatz ( utf8_decode( "Mannschaftsführer - Stadt") )."<br>";
        echo "Tel.: ".$team->get ( "mf_telefon" )."<br>";
        if ( strlen ( $tel = $team->get ( "mf_telefon2" ) ) )
            echo " oder $tel"."<br>";
        echo $team->get ( "mf_email" )."<br>";

    echo "<br /><b>Spiellokal:</b><br />";
        echo $team->get("so_name")."<br />";
        echo $team->get("so_strasse").", ".$team->get("so_plz")." ".$team->get("so_stadt")."";
        if ( strlen ( $tel = $team->get ( "so_telefon" ) ) )
            echo "<br />Telefon: $tel";

        // SO
        echo "<td style='vertical-align: top;  padding-left: 10px; font-family: Verdana; font-size:12pt; width:50%'>";
    echo "<b>Vereinsvorsitzender:</b><br />";
        echo toInline ( getZusatz ( "Vorsitzender - Name" ))."<br />";
        echo toInline ( getZusatz ( "Vorsitzender - Adresse" ))."<br />";
        echo toInline ( getZusatz ( "Vorsitzender - PLZ" ))." ".getZusatz ( "Vorsitzender - Stadt" )."<br>";
        echo "Tel.: ".getZusatz ( "Vorsitzender - Telefon" )."<br />";
        echo toInline ( getZusatz ( "Vorsitzender - eMail" ));
    echo "</td></tr></table><br />";






        // Aufstellung
        echo "<table  cellspacing='0' cellpadding='3' style='font-family: Verdana; font-size:12pt; border-collapse: collapse '>";
        echo "<tr $style><th $style>Nr.</th><th $style>Name</th><th $style>DWZ</th>";
        $r = SED_GetRundenzahl ( $team->get ( "staffel" ) );
        for ( $i = 1; $i <= $r; ++$i )
            echo "<th $style>$i</th>";
        echo "<th $style>Pkt</th></tr>";
        $data = $team->getAufstellung ();
        foreach ( $data as $spieler )
        {
            if ( $spieler ["istErsatz"] ) continue;
            echo "<tr $style>";
            echo "<td $style>&nbsp;$spieler[brettnr]</td>";
            echo "<td $style>".SED_Spielername($spieler)."&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            echo "<td $style align='right'>$spieler[dwz]</td>";
            for ( $i = 1; $i <= $r + 1; ++$i )
                echo "<td $style>&nbsp;&nbsp;&nbsp;&nbsp;</td>";
            echo "</tr>";
        }
        echo "</table>";

        echo "<br /><br />";
    }

}// END



elseif ($_GET["format"]=="nsv-txt") {
// BEGIN SJBH HEFT

    header ( "Content-type: text/plain" );

    $style = "style='font-family: Verdana; font-size:12pt; border:solid; border-color:black; border-collapse:collapse; border-width:1px'";

    // Mannschaften holen. Sortierung Staffel, ZPS
    $second = false;
    $rsrc = mysql_query ( "SELECT id FROM mannschaften m WHERE m.turnier=$globals[tid] AND m.staffel=$_GET[staffel] ORDER BY m.staffel DESC, m.zps", $globals ["db"] );
    while ( $mid = @reset ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) ){

        // Objekt erstellen
        $_GET ['mid'] = $mid;
        $team = new SED_Mannschaft ( $mid );

        // DSB-Namen laden
        $rc = mysql_query ( "SELECT Vereinname FROM dwz_vereine WHERE ZPS='".$team->get("zps")."'", $globals ["db"] );
        $name = @reset ( mysql_fetch_array ( $rc, MYSQL_ASSOC ) );
        $nr = ($team->get("mnr") > 1 ? " ".$team->get("mnr") : "");
        $sname = str_replace ( "BMM ", "", $globals["staffeln"][$team->get("staffel")] );
        echo "$name $nr\n\n";


        // MF
        echo "Mannschaftsf&uuml;hrer\n";
        echo $team->get("mf_name")."\n";
        echo toInline ( getZusatz ( utf8_decode("Mannschaftsführer - Adresse" )))."\n";
        echo toInline ( getZusatz ( utf8_decode("Mannschaftsführer - PLZ" )))." ".getZusatz ( utf8_decode("Mannschaftsführer - Stadt") )."\n";
        echo "Tel.: ".$team->get ( "mf_telefon" )."\n";
        if ( strlen ( $tel = $team->get ( "mf_telefon2" ) ) )
            echo " oder $tel"."\n";
        echo $team->get ( "mf_email" )."\n";

/*
    echo "\nVereinsvorsitzender:\n";
        echo toInline ( getZusatz ( "Vorsitzender - Name" ))."\n";
        echo toInline ( getZusatz ( "Vorsitzender - Adresse" ))."\n";
        echo toInline ( getZusatz ( "Vorsitzender - PLZ" ))." ".getZusatz ( "Vorsitzender - Stadt" )."\n";
        echo "Tel.: ".getZusatz ( "Vorsitzender - Telefon" )."\n";
*/
    echo "\nSpiellokal:\n";
        echo $team->get("so_name")."\n";
        echo $team->get("so_strasse").", ".$team->get("so_plz")." ".$team->get("so_stadt")."";
        if ( strlen ( $tel = $team->get ( "so_telefon" ) ) )
            echo "\nTelefon: $tel";


        // Aufstellung
        echo "\n\nNr.\tName\tDWZ\n";
        $data = $team->getAufstellung ();
        foreach ( $data as $spieler )
        {
            if ( $spieler ["istErsatz"] ) continue;
            echo "$spieler[brettnr]\t";
            echo SED_Spielername($spieler);
            echo "\t$spieler[dwz]\n";
        }
    echo "\n\n";
    }

}// END





else {
// BEGIN FRL HEFT
    header ( "Content-type: text/plain" );

    // Mannschaften holen. Sortierung Staffel, ZPS
    $second = false;
    $rsrc = mysql_query ( "SELECT id FROM mannschaften m WHERE m.turnier=$globals[tid] ORDER BY m.staffel, m.zps", $globals ["db"] );
    while ( $mid = @reset ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) ){

        // Objekt erstellen
        $_GET ['mid'] = $mid;
        $team = new SED_Mannschaft ( $mid );

        // DSB-Namen laden
        $rc = mysql_query ( "SELECT Vereinname FROM dwz_vereine WHERE ZPS='".$team->get("zps")."'", $globals ["db"] );
        $name = @reset ( mysql_fetch_array ( $rc, MYSQL_ASSOC ) );
        $nr = ($team->get("mnr") > 1 ? " ".$team->get("mnr") : "");
        echo $name ."$nr\n";

        // Vorsitzender
        echo "\n";
        echo toInline ( getZusatz ( "Vereinsvorsitzender - Anschrift" ))."\n";
        echo "Tel.: ".getZusatz ( "Vereinsvorsitzender - Telefon" ).", eMail: ".getZusatz ( "Vereinsvorsitzender - E-Mail" )."\n";

        // MF
        echo "Postempf&auml;ngerIn / Mannschaftsf&uuml;hrerIn:\n";
        echo $team->get("mf_name").", ";
        echo toInline ( getZusatz ( utf8_decode("Mannschaftsführer - Anschrift" )))."\n";
        echo "Tel.: ".$team->get ( "mf_telefon" );
        if ( strlen ( $tel = $team->get ( "mf_telefon2" ) ) )
            echo " oder $tel";
        echo ", eMail: ".$team->get ( "mf_email" )."\n";

        // SO
        echo "Spiellokal\n";
        echo $team->get("so_name")."\n".$team->get("so_strasse").", ".$team->get("so_plz")." ".$team->get("so_stadt")."\n";

        // Aufstellung
        echo "\tRangliste\tDWZ\t\t\tRangliste\tDWZ\n";
        $data = $team->getAufstellung ();
        for ( $i = 1; $i <= 6; ++$i )
        {
            $spieler = $data [$i-1];
            echo "$i\t";
            $gast = $spieler["istGastspieler"]?" (G)":"";
            echo SED_Spielername($spieler)."$gast\t";
            echo $spieler["dwz"]."\t\t";

            if ( isset ( $data [$i+6-1] ) ){
                $spieler = $data [$i+6-1];
                echo ($i+6)."\t";
                $gast = $spieler["istGastspielerin"]?" (G)":"";
                echo SED_Spielername($spieler)."$gast\t";
                echo $spieler["dwz"] ? $spieler ["dwz"] : " ";
            }
            else
                echo " \t \t \t";
            echo "\n";
        }

        echo "\n\n\n";

        if ( $second )
            echo "===\n\n\n";

        $second = !$second;
    }


  // Abfragen
foreach ( $globals ["staffeln"] as $staffel=>$dummy ){
  $res = mysql_query ( "SELECT paarungen.runde, mannschaft1, mannschaft2, erg1, erg2, erg1 IS NOT NULL AND erg2 IS NOT NULL as isset, IF(termin IS NULL,'',DATE_FORMAT(termin,'(am %d.%m.)')) as terminAbw FROM paarungen WHERE staffel=$staffel ORDER BY paarungen.runde,paarungen.id", $globals ['db'] );
  if ( $res && mysql_num_rows ( $res ) )
  {
    echo "Terminplan 2010 / 2011\n";

      // Paarungen durchgehen
      $lastr = false;
      while ( $paarung = mysql_fetch_array ( $res, MYSQL_ASSOC ) )
      {
        // Nächste Runde?
        if ( $paarung ['runde'] != $lastr )
        {
          // Datum berechnen
          $paarung ['termin'] = SED_GetTermin ( $paarung ['runde'], $staffel );

          // Ausgabe der Spieltagüberschrift
          echo "\n$paarung[runde]. Runde am $paarung[termin]\t";
          $lastr = $paarung ["runde"];
        }
        else
            echo " \t";

        // Paarungsausgabe
        echo $globals["teams"]  [$paarung ['mannschaft1']] ;
        echo "\t-\t";
        echo $globals["teams"] [ $paarung ['mannschaft2']] ;
        echo "\t \n";
      }
  }
}


}// END FRL
?>
