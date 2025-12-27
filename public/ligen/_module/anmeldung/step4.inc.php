<?php
/* Mannschaftsmeldung: 4. Spieler auswählen
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage anmeldung
 */
    require_once ( "ajax.inc.php" );

    // Zus&auml;tzliche Mailempf&auml;nger verarbeiten (Step 3)
    $anmeldung->setZusatzEmpfaenger ( $_POST ["zusatzempfaenger"] );

    // Informationen
    ?>
    <b>Bitte machen Sie vor den Spielern, die Sie anmelden m&ouml;chten, einen Haken.</b>
    Im Folgenden sind alle Mitglieder Ihres Vereines aufgelistet, die in der Mitgliederdatenbank
    gefunden wurden.
    Die Reihenfolge muss nicht Ihrer gew&uuml;nschten Reihenfolge der Mannschaftsaufstellung entsprechen,
    diese wird erst im n&auml;chsten Schritt festgelegt.
    Spieler, die hier nicht aufgelistet sind, k&ouml;nnen Sie im Formular weiter unten auf dieser Seite
    eintragen.
    <br /><br />
    <?php

    // Ausgabe der DWZ-Liste
    echo "<table cellspacing='0' cellpadding='3'>";
    echo "<tr><th>Spieler</th><th>&nbsp;&nbsp;DWZ</th><th>&nbsp;&nbsp;ELO</th><th>&nbsp;&nbsp;Geburt</th></tr>";
    $players = $anmeldung->getDwzList ();
    for ( $i = 0; $i < count ( $players ); ++$i ){
		global $prefs;
		$checked = ($prefs['organisation'] == "7p" && $i<40) ? "checked='checked'" : ""; // NSV Pokal?
        echo "<tr>
            <td><input type='checkbox' id='spieler$i' name='spieler$i' value='".base64_encode($players[$i]->getJSON ())."' onClick='AddTimestamp($i);' $checked />
                <input type='hidden' name='timestamp$i' value='' />
                &nbsp;<label for='spieler$i' id='spielerlabel$i'>".$players[$i]->getName()."</label>&nbsp;&nbsp;</td>
            <td>&nbsp;&nbsp;".$players[$i]->get("dwz")."</td>
            <td>&nbsp;&nbsp;".$players[$i]->get("elo")."</td>
            <td>&nbsp;&nbsp;".$players[$i]->get("geburt")."</td></tr>";
    }
    echo "</table>";

    // Die Anzahl an den n&auml;chsten Schritt weitergeben
    echo "<input type='hidden' name='spielerCount' value='".count($players)."' />";

    // VS Spieler / Gastspielerinnen
    // Infotext und Überschriften
    ?>
    <br /><span class='sed_hl2'>Weitere Spieler</span><br /><br />
    <b>In die folgenden Zeilen k&ouml;nnen Sie die Spielerdaten von Spielern eingeben,
    die nicht aufgelistet waren.</b> Dies k&ouml;nnen Spieler mit vorl&auml;ufiger Spielgenehmigung,
    oder auch Gastspieler sein. Die Spielberechtigung wird vom Turnierleiter gepr&uuml;ft werden.
    Die Reihenfolge muss nicht Ihrer gew&uuml;nschten
    Reihenfolge der Mannschaftsaufstellung entsprechen.<br /><br />
    <table cellspacing='0' cellpadding='3'>
    <tr><th>Name</th><th>Vorname</th><th>Gebjahr</th></tr>
    <?php

    // Zeilen ausgeben
    $vs_count = count ( $players ) < 3 ? max ( array ( "10", 3 * $prefs ['brettzahl'] ) ) : 8;
    for ( $i = 0; $i < $vs_count; ++$i )
    {
      echo "<tr><td><input type='text' name='vs_nachname_$i' size='20' maxlength='20' onkeyup='OnVsChange($i);' />&nbsp;&nbsp;<div name='vs_recommended_$i'></div></td>
                <td style='vertical-align:top'><input type='text' name='vs_vorname_$i' size='20' maxlength='20' onkeyup='OnVsChange($i);' />&nbsp;&nbsp;</td>
                <td style='vertical-align:top'><input type='text' name='vs_geburt_$i' size='4' maxlength='4' /></td>
                <input type='hidden' name='vs_timestamp_$i' value='99' />
                <input type='hidden' name='vs_zps_$i' value='' />
            </tr>";
    }
    echo "</table><br />";

    // Die Anzahl an n&auml;chsten Schritt weitergeben
    echo "<input type='hidden' name='vs_count' value='$vs_count' />";

    // Javascript implementieren
    ?>
    <script type="text/javascript"><!--

        var AddTimestamp_static = 1;
        var lastVSchange = 1;
            
        // Ein Spieler wurde aus der Liste ausgew&auml;hlt
        function AddTimestamp ( id )
        {
            obj = document.getElementsByName ( "timestamp" + id ) [0];
            obj.setAttribute ( "value", AddTimestamp_static );
            ++AddTimestamp_static;
        }

        // Der Name eines VS-Spielers wurde ge&auml;ndert
        function OnVsChange ( id )
        {
            // Spielerliste der letzten VS verstecken
            if ( id != lastVSchange ){
                ShowRecommendedList ( lastVSchange, "none" );
                lastVSchange = id;
            }

            // Timestamp speichern
            AddTimestampToVs ( id )
            
            // Bisherige zps löschen
            document.getElementsByName ( "vs_zps_"+id )[0].value = "";
            
            // Ajax Request stellen
            {
                // Aktuellen Namen zusammensetzen
                nachname = document.getElementsByName ( "vs_nachname_"+id ) [0].value;
                vorname = document.getElementsByName ( "vs_vorname_"+id ) [0].value;
                if ( vorname.length > 0 )
                    nachname = nachname + "," + vorname;

                // Ist die aktuelle Eingabe lang genug f&uuml;r eine Anfrage?
                if ( nachname.length > 1 )
                {
                    <?php
                        $ajax = new SED_AjaxRequest ( "getSpieler" );
                        $ajax->setOption ( "name", "nachname" );
                        $ajax->setOption ( "verband", "'$prefs[anmVerband]'" );
                        $ajax->onResult ( '
                            // HTML zusammensetzen
                            html = "<i>Meinten Sie:</i><br />" + @RESULT@ + "<br />";

                            // Kein Spieler gefunden?  
                            if ( @RESULT@.length < 15 ) 
                                html = "";  

                            // Wirklich setzen
                            document.getElementsByName ( "vs_recommended_"+lastVSchange ) [0].innerHTML = html;
                            ShowRecommendedList ( lastVSchange, "block" );
                        ' );
                        echo $ajax->getJavascript ();
                    ?>
                }
            }
        }

        // Es wurde auf einen Namen geklicht
        function OnSpielerClick ( json )
        {
            id = lastVSchange;
            
            // Felder setzen
            <?php
                foreach ( array ( "nachname", "vorname", "geburt", "zps" ) as $feld ){
                    echo "document.getElementsByName ( 'vs_$feld'+'_'+id ) [0].value = json.$feld;";
                }
            ?>
        }

        // Zeigt eine Vorschlagsliste an bzw. versteckt sie
        function ShowRecommendedList ( id, style )
        {
            document.getElementsByName ( "vs_recommended_"+id )[0].style.display = style;
        }
        
        function AddTimestampToVs ( id )
        {
            document.getElementsByName ( "vs_timestamp_" + id ) [0].value = AddTimestamp_static;
            ++AddTimestamp_static;
        }

    --></script>
    <?php	  
?>
