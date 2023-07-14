<?php
// 2023-07-14: Still seems to be in use.

$style = "style='font-family: Verdana; font-size:12pt; border:solid; border-color:black; border-collapse:collapse; border-width:1px'";

// Mannschaften holen. Sortierung Staffel, ZPS
global $globals;
$second = false;
$rsrc = mysql_query ( "SELECT id FROM mannschaften m WHERE m.turnier=$globals[tid] AND m.staffel=$_GET[staffel] ORDER BY m.staffel DESC, m.zps, m.mnr", $globals ["db"] );
while ( $mid = mysql_fetch_array ( $rsrc, MYSQL_ASSOC )) {
    $mid = @reset($mid);

    // Objekt erstellen
    $_GET ['mid'] = $mid;
    $team = new SED_Mannschaft ( $mid );

    // DSB-Namen laden
    if (strlen($team->get('zps')) == 5) {
        $rc = mysql_query ( "SELECT Vereinname FROM dwz_vereine WHERE ZPS='".$team->get("zps")."'", $globals ["db"] );
        $name = @reset ( mysql_fetch_array ( $rc, MYSQL_ASSOC ) );
    } else {
        $name = $team->get('name');
    }
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