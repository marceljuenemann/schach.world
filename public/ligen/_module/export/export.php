<?php

/**
 * Exports are deprecated and will be removed in the future.
 * If there is a need for it in the future, I'd recommend to use the public JSON API.
 */
global $globals;
@$_GET['auth'] === substr($globals['masterpasswort'], 0, 8) or die('Bitte wenden Sie sich an webmaster@nsv-online.de für Exporte');

    require_once ( "turnier.inc.php" );
    require_once ( "mannschaft.class.php" );

    function getZusatz ( $feld ){
        $mid = $_GET ["mid"];
        return SED_Query("
          SELECT inhalt
          FROM anmeldungZusatzfelder
          WHERE feldname LIKE ?
            AND mannschaft=?",
          [$feld, $mid]
        )->fetchOne();
    }

    function toInline ( $str ){
        return str_replace ( "\n", ", ", str_replace ( "\r\n", "\n", trim ( $str ?: '' ) ) );
    }

    function replaceUmlauts ( $str ){
    return str_replace(
      array(utf8_decode('ä'), utf8_decode('ö'), utf8_decode('ü'), utf8_decode('ß')),
      array('ae', 'oe', 'ue', 'ss'),
      $str
    );
  }

if ( $_GET ["format"] == "u12spielplaner"){
  require_once("../_module/export/u12spielplaner.inc.php");
}

else if ( $_GET ["format"] == "nsv" ){
    require_once("../_module/export/nsv.inc.php");
  }
  
else if ( $_GET ["format"] == "702" ){
  require_once("../_module/export/bezirk2.inc.php");
}

else if ( $_GET["format"] == "701" || $_GET["format"] == "peter" ){
// 2023-08-14: Still used by Peter for both NSV and Bezirk 1.

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
    exit; // No other headers.


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

elseif ($_GET["format"]=="nsv-txt") {
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

}

?>
