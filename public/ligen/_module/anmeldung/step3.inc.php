<?php
/* Mannschaftsmeldung: 3. Mannschaftsführer und zusätzliche Daten
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

    // Mannschaftsführer Form
    $frmSO = array (
        // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
        array ( "mf_name", "Name", "Der vollst&auml;ndige Name des Mannschaftsf&uuml;hrers.", 30, 40, false ),
        array ( "mf_email", "Emailadresse", "Die Emailadresse, &uuml;ber die wichtige Informationen und Rundmails verschickt werden. Bitte geben Sie eine Adresse an, die regelm&auml;&szlig;ig abgerufen wird.", 30, 50, false ),
        array ( "mf_telefon", "Telefon", "Die Telefonnummer des Mannschaftsf&uuml;hrers, &uuml;ber die er von anderen Mannschaften oder dem Staffelleiter kontaktiert werden kann.", 15, 30, false ),
        array ( "mf_telefon2", "Telefon alternativ", "Eine zweite Telefonnummer, wie z.B. Mobiltelefon. Dieses Feld ist optional", 15, 30, false )
    );
    
    // Mannschaftsführer finden
    $so = $anmeldung->getMF ();
    
    // Felder ausgeben
    // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
    for ( $i = 0; $i < count ( $frmSO ); ++$i )
    {
        // Titel und Beschriftung
        echo "<span style='font-weight: bold'>Mannschaftsf&uuml;hrer - " . $frmSO [$i][1] . " " . ( $frmSO [$i][5] ? "*" : "" ) . "</span><br />";
        echo $frmSO [$i][2] . "<br />";

        // Wert und Input
        $value = $so [$frmSO [$i][0]];
        $jscript = ( $i == 0 ? "onkeyup='GetMfData(this);'" : "onchange='mf_changed=true'" ); 
        echo "<input $jscript type='text' style='margin-bottom: 15px; margin-top: 5px;' name='" . $frmSO [$i][0] . "' value='$value' size='" . $frmSO [$i][3] . "' maxlength='" . $frmSO [$i][4] . "' /><br />";
    }

    // Javascript zum Mannschaftsführer
    ?>
    <script type='text/javascript'><!--
    
    var mf_changed = false;

    function GetMfData ( obj )
    {
        // Daten abfragen, wenn der Name ok ist
        if ( obj.value.length < 5 ) return;
        if ( mf_changed ) return; // Wurden die Daten schon gesetzt?

        <?php
            $ajax = new SED_AjaxRequest ( "getMF" );
            $ajax->setOption ( "name", "obj.value" );
            $ajax->onResult ( '
                // Antwort spliten
                var data = @RESULT@.split ( ";" );

                // Daten setzen
                document.getElementsByName ( "mf_telefon" ) [0].value = data [0];
                document.getElementsByName ( "mf_telefon2" ) [0].value = data [1];
                document.getElementsByName ( "mf_email" ) [0].value = data [2];
            ' );
            echo $ajax->getJavascript ();
        ?>
    }

    --></script>
    <?php

    // Zusatzfelder
    $default = $anmeldung->getZusatzFelderDefault ();
    foreach ( $anmeldung->getZusatzFelder () as $feldid=>$feld )
    {
        // Feldnamen ausgeben
        $pos = strpos ( $feld [0], ":" );
        $pos = $pos ? $pos : strlen ( $feld [0] );
        echo "<b>".substr ( $feld [0], 0, $pos+1 )."</b>";
        echo substr ( $feld [0], $pos + 1 )."<br />";
        
        // Inhalt bekannt?
        $content = isset ( $default [$feld [0]] ) ? $default [$feld[0]] : "";
        
        // Feld ausgeben
        if ( $feld [1] == 0 )
            echo "<textarea cols='40' rows='5' name='$feldid'>$content</textarea><br /><br />";
        else
            echo "<input type='text' size='$feld[1]' name='$feldid' value='$content' /><br /><br />";
    }

    // Textfeld für zusätzliche Empfänger
    ?>
    <b>Zus&auml;tzliche Mailempf&auml;nger</b><br />Tragen Sie in das folgende Textfeld alle Mailadressen ein, die die an den Mannschaftsf&uuml;hrer gesendeten Infos, wie Rundmails oder Eingabelinks, ebenfalls erhalten sollen. Nutzen Sie dabei pro Mailadresse nur eine Zeile.<br />
    <textarea name='zusatzempfaenger' cols='40' rows='3'></textarea><br /><br />
    <?php
?>
