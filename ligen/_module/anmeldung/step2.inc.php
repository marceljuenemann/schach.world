<?
/* Mannschaftsmeldung: 2. Spiellokal
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage anmeldung
 */
    // Spielgemeinschaft Feld verarbeiten (Step 1)
    if ( isset ( $_POST ['spielgemeinschaft'] ) )
        $anmeldung->setFields ( array ( "zps" => $anmeldung->get("zps").$_POST['spielgemeinschaft'] ) );

    // Spielort Form
    $frmSO = array (
        // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
        array ( "so_name", "Name", "Der Name des Spiellokals, zum Beispiel 'Haus der Vereine'.", 30, 50, false ),
        array ( "so_strasse", "Stra&szlig;e", "Die Stra&szlig;e und die Hausnummer des Spiellokals.", 30, 30, false ),
        array ( "so_plz", "PLZ", "Die Postleitzahl der Stadt in dem sich das Spiellokal befindet.", 5, 5, false ),
        array ( "so_stadt", "Stadt", "Die Stadt und ggf. der Ortsteil.", 15, 20, false ),
        array ( "so_telefon", "Telefonnummer", "Wenn das Spiellokal &uuml;ber ein Telefon verf&uuml;gt, k&ouml;nnen Sie die Nummer hier angeben.", 15, 15, false )
    );

    // Spiellokal des Vereins finden
    $so = $anmeldung->getSO ();
    
    // Felder ausgeben
    // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
    for ( $i = 0; $i < count ( $frmSO ); ++$i )
    {
        // Titel und Beschriftung
        echo "<span style='font-weight: bold'>" . $frmSO [$i][1] . " " . ( $frmSO [$i][5] ? "*" : "" ) . "</span><br />";
        echo $frmSO [$i][2] . "<br />";

        // Wert und Input
        $value = $so [$frmSO [$i][0]];
        echo "<input type='text' style='margin-bottom: 15px; margin-top: 5px;' name='" . $frmSO [$i][0] . "' value='$value' size='" . $frmSO [$i][3] . "' maxlength='" . $frmSO [$i][4] . "' /><br />";
    }
    
    // Nur Mannschaft eingeben?
    if ( $isTurnierleiter )
        echo "<input type='checkbox' id='ohneauf' name='ohne_aufstellung' value='1' /> <label for='ohneauf'>Jetzt keine Aufstellung eingeben, sondern sp&auml;ter nachtragen</label><br /><br />";
?>
