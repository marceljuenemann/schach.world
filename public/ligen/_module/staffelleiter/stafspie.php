<?
/* SL-Bereich: Spieldaten bearbeiten
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

    require_once ( "login.inc.php");
    require_once ( "dwzdb.inc.php" );
    require_once ( "mannschaft.class.php" );
    require_once ( "spieler.class.php" );
    require_once ( "ajax.inc.php" );

    // Vorbereitungen
    $team = new SED_Mannschaft ( $_GET ['mid'] );
    echo "<span class='sed_hl2'>".$globals['teams'][$_GET['mid']]."</span><br /><br />";

    // Berechtigung
    if ( $admin ["staffel"] && $team->get("staffel") && $admin ["staffel"]!=$team->get("staffel") )
        SED_Error ( "Sie sind nicht berechtigt, diese Mannschaft zu bearbeiten!", true );

    ////////////////////////////////////////////////////////
    // Spieler bearbeiten (Formular)
    ////////////////////////////////////////////////////////

    if ( isset ( $_GET ['edit'] ) ){
        $spieler = new SED_Spieler ();
        
        // Änderung, also keine Nachmeldung?
        if ( $_GET ['edit'] ){
            $spieler->set ( "id", $_GET ['edit'] );
            $spieler->autofill ();
            $action  = "Spielerdaten &auml;ndern";
        }
            
        // Nachmeldung?
        else {
            require_once ( "runde.inc.php" );
            $spieler->set ( "nmR", $_GET ['r'] );
            $spieler->set ( "geschlecht", "m" );
            $spieler->set ( "nmSid", $team->get("staffel") );
            $action = "Spieler nachmelden";
        }
        
        // Nachmeldungs-Runde vorbereiten
        $nmR = "<option value=''>Regul&auml;rer Spieler</option>";
        for ( $r = 1; $r <= $prefs ['runden']; ++$r )
            $nmR .= "<option value='$r'>Nachmeldung $r. Spieltag</option>";

        // Formular Eigentschaften
        $form = array (
            // feld, Titel, size/options
            array ( "nachname", "Nachname", 20 ),
            array ( "vorname", "Vorname", 20 ),
            array ( "titel", "Titel", 5 ),
            array ( "geburt", "Geburt", 10 ),
            array ( "nmR", "Nachmeldung", $nmR ),
            array ( "geschlecht", "Geschlecht", "<option value='m'>M</option><option value='w'>W</option>" ),
            array ( "zps", "ZPS-Nummer", 10 ),
            array ( "dwz", "DWZ", 4 ),
            array ( "elo", "ELO", 4 )
        );

        // Formular ausgeben
        echo "<form accept-charset='ISO-8859-1' action='?admin=stafspie-$admin[userid]-$admin[session]&mid=$_GET[mid]&save=$_GET[edit]' method='post'><fieldset><legend>$action</legend><table>";
        for ( $row = 0; $row < 3; ++$row ){
            echo "<tr>";
            for ( $col = 0; $col < 3; ++$col ){
                $feld = $form [3*$row+$col];
                
                // Beschriftung
                echo "<td style='vertical-align:top'><b>$feld[1]:</b><br />";
                
                // Textfeld oder Select?
                $value = $spieler->isFieldSet ( $feld [0] ) ? $spieler->get ( $feld [0] ) : "";
                if ( is_numeric ( $feld [2] ) ){
                    echo "<input type='text' name='$feld[0]' value='$value' size='$feld[2]' onkeyup='OnSpielerdatenChange(\"$feld[0]\")' />";
                } else {
                    echo "<select name='$feld[0]' onchange='OnSpielerdatenChange(\"$feld[0]\")'>".SED_SelectOption($feld[2],$value)."</select>";
                }
                
                // Platzhalter für Spielervorschläge
                if ( $feld [0] == "nachname" )
                    echo "<div name='ajax_placeholder'></div>";
                echo "</td>";
            }
            echo "</tr>";
        }
        echo "</table><br />";

        // Nicht änderbare Spielerdaten
        if ( $_GET ['edit'] ){
            echo "<span style='float:right'><i>Brett-Nr: ".$spieler->get("brettnr").", ID: ".$spieler->get("id")."</i></span>";
            echo "<input type='hidden' name='id' value='".$spieler->get("id")."' />
                  <input type='hidden' name='brettnr' value='".$spieler->get("brettnr")."' />";
        }

        // Speicher-Button
        echo "<input type='hidden' name='nmSid' value='".$spieler->get("nmSid")."' />";
        echo "<input type='submit' class='sed_submit' value='$action' />";
        echo "</fieldset></form><br /><br />";
        
        // Javascript für Spielervorschläge
        ?>
        <script type='text/javascript'><!--

        function OnSpielerdatenChange ( edit )
        {
            // Nur bei manchen Aenderungen Anfrage starten
            if ( edit != "nachname" && edit != "vorname" && edit != "zps" && edit != "dwz" )
                return;
                
            // Aktuellen Namen zusammensetzen
            nachname = document.getElementsByName ( "nachname" ) [0].value;
            vorname = document.getElementsByName ( "vorname" ) [0].value;
            if ( vorname.length > 0 )
                nachname = nachname + "," + vorname;

            // Ist die aktuelle Eingabe lang genug fuer eine Anfrage?
            if ( nachname.length > 1 )
            {
                <?
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
                        document.getElementsByName ( "ajax_placeholder" ) [0].innerHTML = html;
                    ' );
                    echo $ajax->getJavascript ();
                ?>
            }
        }

        // Ein Spieler zur Vervollstaendigung wurde gewaehlt
        function OnSpielerClick ( json )
        {
            // Felder setzen
            <?
                foreach ( $form as $feld ){
                    if ( $feld[0] != "nmR"  )
                        echo "if ( json.$feld[0] ) document.getElementsByName ( '$feld[0]' ) [0].value = json.$feld[0];";
                }
            ?>
        }
        --></script>
        <?
    }

    ////////////////////////////////////////////////////////
    // Spieler bearbeiten (Speicherlogik)
    ////////////////////////////////////////////////////////

    if ( isset ( $_GET ['save'] ) )
    {
        // Daten aus POST laden
        $spieler = new SED_Spieler ();
        foreach ( $_POST as $name=>$value ){
            try {
                $spieler->set ( $name, $value );
            } catch ( UnknownFieldException $e ) {
                SED_Error ( "Da ist einiges schief gelaufen! Wahrscheinlich ein Programmierfehler...", true );
            } catch ( WrongFormatException $e ) {
                SED_Error ( $e->getMessage () );
            }
        }

        // Ist der Benutzer zur Eingabe berechtigt? staffel=0 beachten!
        if ( $spieler->isFieldSet ( "id" ) ){ // Keine Nachmeldung
            $staffel = SED_Value ( "SELECT m.staffel FROM spieler s INNER JOIN mannschaften m ON m.id=s.mannschaft WHERE s.id=? LIMIT 1", [$spieler->get("id")] );
            if ( $staffel && !isset ( $globals ['staffeln'][$staffel] ) )
                SED_Error ( "Die Staffel des Spielers geh&ouml;rt nicht zum Turnier!", true );
            if ( $staffel && $admin ['staffel'] && $staffel != $admin['staffel'] )
                SED_Error ( "Die Staffel des Spielers geh&ouml;rt nicht zur Staffel!", true );
        }


        // In Datenbank speichern
        try {
            // Mannschaft erst jetzt setzen
            $spieler->set ( "mannschaft", $team->get ( "id" ) );
            $spieler->saveToDB ( $_GET ['save'], $_GET ['mid'] );

            // Erfolgsmeldung
            echo "<b>Neue Spielerdaten gespeichert</b><br /><br />";
        } catch ( Exception $e ) {
            SED_Error ( "Fehler beim Speichern: ".$e->getMessage ()."<br /><a href='javascript:history.back()'>Zur&uuml;ck zum Formular</a>" );
        }

        // Mannschaftsdaten neu laden
        $team = new SED_Mannschaft ( $_GET ['mid'] );
        
    }

    ////////////////////////////////////////////////////////
    // Löschen
    ////////////////////////////////////////////////////////

    if ( isset ( $_GET ['del'] ) )
    {
        // Wurde der Spieler bereits eingesetzt?
        $rsrc = mysql_query ( "SELECT id FROM spielerpaarungen WHERE spieler1=$_GET[del] or spieler2=$_GET[del]", $globals ['db'] ); 
        if ( mysql_num_rows ( $rsrc ) )
            SED_Error ( "Der Spieler konnte nicht gel&ouml;scht werden, da er bereits in einem Spiel eingesetzt wurde." );
        else {
            // Lösche, wenn die Berechtigung vorhanden ist.
            if ( !mysql_query ( "DELETE FROM spieler WHERE id=$_GET[del] AND mannschaft=$_GET[mid] LIMIT 1", $globals ['db'] ) )
                SED_Error ( "Der Spieler konnte nicht gel&ouml;scht werden. Evtl. fehlt Ihnen die Berechtigung dazu.", true );
                
            // Gab es den Spieler überhaupt nicht?
            if ( !mysql_affected_rows () )
                SED_Error ( "Den angegebenen Spieler gab es nicht (mehr)!", true );

            // Brett-Nummern aufrücken lassen
            if ( !mysql_query ( "UPDATE spieler SET brettnr=brettnr-1 WHERE mannschaft=$_GET[mid] AND brettnr>$_GET[bnr]", $globals ['db'] ) )
                SED_Error ( "Fehler beim &Auml;ndern der nachfolgenden Brett-Nummern", true );

            // Mannschaftsdaten neu laden
            $team = new SED_Mannschaft ( $_GET ['mid'] );
        
            // Cache löschen
            SED_Cache::clearTeam ( $_GET ['mid'], SED_Cache::TEAM_AUFSTELLUNG );
            SED_Cache::clearSpieltag ();
            echo "<b>Spieler gel&ouml;scht</b><br /><br />";
        }
    }

    ////////////////////////////////////////////////////////
    // Spieler tauschen
    ////////////////////////////////////////////////////////

    if ( isset ( $_GET ['swap'] ) )
    {
        // Überprüfen, ob es den Tauschpartner gibt
        if ( $partner = SED_Query ( "SELECT id FROM spieler WHERE mannschaft=? AND brettnr=? LIMIT 1", [$_GET['mid'], $_GET['with']] )->fetchOne() )
        {
            // Neue Brett-Nummer setzen
            if ( !mysql_query ( "UPDATE spieler SET brettnr=$_GET[with] WHERE mannschaft=$_GET[mid] AND brettnr=$_GET[swap] LIMIT 1", $globals ['db'] ) )
                SED_Error ( "Der Spieler konnte nicht gefunden werden!", true );
                
            // Neue Brett-Nummer setzen Nr. 2
            if ( !mysql_query ( "UPDATE spieler SET brettnr=$_GET[swap] WHERE id=".$partner." LIMIT 1", $globals ['db'] ) )
                SED_Error ( "Der Spieler konnte nicht gefunden werden!", true );
                
            // Mannschaftsdaten neu laden
            $team = new SED_Mannschaft ( $_GET ['mid'] );

            // Cache löschen
            SED_Cache::clearAll();
            echo "<b>Spieler getauscht</b><br /><br />";
        } else {
            SED_Error ( "Es wurde kein Tauschpartner in der Mannschaft gefunden. Hinweis: Sie k&ouml;nnen einen Spieler nicht mit einem Spieler aus der Ersatzmannschaft tauschen", false );
        }
    }
    
    ////////////////////////////////////////////////////////
    // Aufstellung
    ////////////////////////////////////////////////////////

    echo "<table cellspacing='0' cellpadding='3' class='sed_tabelle'>";
    echo "<tr><th>Nr.</th><th>Name</th><th>DWZ</th><th></th></tr>";

    foreach ( $team->getAufstellung () as $spieler )
    {
        // Nr.
        echo "<tr><td class='r'>$spieler[brettnr]</td>";

        // Name mit Bearbeitungslink
        echo "<td class='l'>";
        echo "<a href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$spieler[mannschaft]&edit=$spieler[id]'>";
        echo "<img src='$globals[systemicons]desk_bearbeiten.png' alt='Bearbeiten' class='sed_admin_icon' />";
        echo SED_Spielername($spieler)."</a></td>";

        // DWZ
        echo "<td>$spieler[dwz]</td>";
        echo "<td class='l'>";

        // Spieler tauschen
        $up = $spieler ["brettnr"] - 1;
        $down = $spieler ["brettnr"] + 1;
        echo "<a href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$spieler[mannschaft]&swap=$spieler[brettnr]&with=$up'><img src='$globals[systemicons]up.gif' alt='Nach oben verschieben' class='sed_admin_icon' style='margin-right:0' /></a> ";
        echo "<a href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$spieler[mannschaft]&swap=$spieler[brettnr]&with=$down'><img src='$globals[systemicons]down.gif' alt='Nach unten verschieben' class='sed_admin_icon' style='margin-right:0' /></a> ";
        
        // Löschen
        if ( $admin ['usertype'] == 't' || $spieler ['berechtigtAb'] )
            echo "<a href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$spieler[mannschaft]&del=$spieler[id]&bnr=$spieler[brettnr]'><img src='$globals[systemicons]desk_loeschen.png' alt='Loeschen' class='sed_admin_icon' /></a>";
        echo "</td></tr>";
    }
    echo "</table><br />";

    // Nachmeldungslink
    echo "<img src='$globals[systemicons]desk_nachmeldung.png' alt='Spieler nachmelden' class='sed_admin_icon' />";
    echo "<a href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$_GET[mid]&edit=0'>";
    echo "Spieler nachmelden</a>";

?>
