<?
/* Mannschaftsmeldung: 5. Reihenfolge festlegen
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage anmeldung
 */
?>
    Bringen Sie in diesem Schritt alle angemeldeten Spieler in die richtige
    Reihenfolge. Durch Klicken auf einen der Pfeile, k&ouml;nnen Sie einen
    Spieler nach oben oder unten verschieben.<br /><br />
    <?

    // Platz für alle Spieler ( timestamp => spieler )
    $spieler = array ();

    // Per Checkbox ausgewählte Spieler speichern
    for ( $i = 0; $i < $_POST ['spielerCount']; ++$i )
        if ( isset ( $_POST ["spieler$i"] ) )
        {
            $sp = new SED_Spieler ();
            $sp->parseJSON ( base64_decode ( $_POST ["spieler$i"] ) );
            $spieler [sprintf ("[%03s][%03d]",$_POST ["timestamp$i"],$i)] = $sp;
        }

    // Per Textfeld eingegeben Spieler
    for ( $i = 0; $i < $_POST ['vs_count']; ++$i )
        if ( $_POST ["vs_nachname_$i"] )
        {
            // Spielerobjekt erzeugen
            $sp = new SED_Spieler ();
            foreach ( array ( "nachname","vorname","geburt" ) as $feld ){
                $sp->set ( $feld, $_POST ["vs_".$feld."_$i"] );
            }

            // ZPS gesetzt? (per AJAX)
            if ( $_POST ["vs_zps_$i"] ){
                $sp->set ( "zps", $_POST ["vs_zps_$i"] );
            }

            // Aus DWZ-Datenbank laden
            $sp->autofill ();

            // Spieler speichern
            $spieler [sprintf ("[%03s]",$_POST ["vs_timestamp_$i"])] = $sp;
        }

    // Spieler nach Timestamp sortieren
    ksort ( $spieler );

    // Erste Brett-Nummer berechnen
    $bnr = $prefs ['spielDreistelligeNr'] * 100 * $anmeldung->get("mnr") + 1;

    // Spieler Tabelle ausgeben
    echo "<table class='sed_tabelle' cellspacing='0' cellpadding='3'>";
    echo "<tr><th>Nr.</th><th>Name</th><th>DWZ</th><th style='min-width:120px'>Verschieben</th></tr>";
    foreach ( $spieler as $player )
    {
        // Brett-Nummer und Name und DWZ
        echo "<tr><td>$bnr</td><td class='l'>
            <span id='label_$bnr'>".$player->getName()."</span>
            &nbsp;&nbsp;&nbsp;&nbsp;</td>
            <td id='dwz_$bnr'>".($player->isFieldSet("dwz")?$player->get("dwz"):"")."</td>";

        // Verschiebe-Buttons und Spielerobjekt
        echo "<td>
            <a href='javascript:AnmeldungRowUp($bnr);' id='upimg_$bnr'><img class='sed_admin_icon' src='$globals[systemicons]up.gif' alt='Nach oben' /></a>
            <a href='javascript:AnmeldungRowDown($bnr);' id='downimg_$bnr'><img class='sed_admin_icon' src='$globals[systemicons]down.gif' alt='Nach unten' /></a>
            <a href='javascript:AnmeldungMoveTo($bnr);' id='rightimg_$bnr'><img class='sed_admin_icon' src='$globals[systemicons]right2.png' alt='An bestimmtes Brett' /></a>
            <input type='hidden' name='spieler_$bnr' value='".base64_encode($player->getJSON())."' />
            </td></tr>";

        ++$bnr;
    }
    echo "</table><br />";

    // Javascript implementieren
    ?>
    <script type="text/javascript"><!--

    function AnmeldungSwap ( idA, idB )
    {
        // Label vertauschen
        var labelA = document.getElementById ( "label_" + idA );
        var labelB = document.getElementById ( "label_" + idB );
        tmp = labelA.innerHTML;
        labelA.innerHTML = labelB.innerHTML;
        labelB.innerHTML = tmp;

        // DWZ vertauschen
        var labelA = document.getElementById ( "dwz_" + idA );
        var labelB = document.getElementById ( "dwz_" + idB );
        tmp = labelA.innerHTML;
        labelA.innerHTML = labelB.innerHTML;
        labelB.innerHTML = tmp;

        // Daten vertauschen
        var dataA = document.getElementsByName ( "spieler_" + idA ) [0];
        var dataB = document.getElementsByName ( "spieler_" + idB ) [0];
        tmp = dataA.value;
        dataA.value = dataB.value;
        dataB.value = tmp;
    }

    function AnmeldungRowDown ( $id, $nofocus )
    {
        if ( document.getElementsByName ( "spieler_" + ( $id + 1 ) ).length )
        {
            AnmeldungSwap ( $id, $id + 1 );
            if ( arguments.length == 2 )
                document.getElementById ( "downimg_" + ( $id + 1 ) ).focus ();
        }
    }

    function AnmeldungRowUp ( $id, $nofocus )
    {
        if ( $id > 1 )
        {
            AnmeldungSwap ( $id - 1, $id );
            if ( arguments.length == 2 )
                document.getElementById ( "upimg_" + ( $id - 1 ) ).focus ();
        }
    }

    // Diese Funktion verschiebt einen Spieler an eine bestimmte Stelle
    function AnmeldungMoveTo ( $id )
    {
        $pos = parseInt ( prompt ( "An welches Brett soll der Spieler verschoben werden?", $id ) );
        if ( $pos < $id )
            for ( $i = $id; $i > $pos; --$i )
                AnmeldungRowUp ( $i );
        else
            for ( $i = $id; $i < $pos; ++$i )
                AnmeldungRowDown ( $i );
    }

    --></script>
