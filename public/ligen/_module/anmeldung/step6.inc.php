<?php
/* Mannschaftsmeldung: 6. In Datenbank speichern
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage anmeldung
 */
    // Spieler-Sortierung verarbeiten
    $bnr = $prefs ['spielDreistelligeNr'] * $anmeldung->get("mnr") * 100 + 1;
    for ( ; isset ( $_POST ["spieler_$bnr"] ); ++$bnr ){
        // Spieler-Objekte einlesen
        $spieler = new SED_Spieler ();
        $spieler->parseJSON ( base64_decode ( $_POST ["spieler_$bnr"] ) );
        $spieler->set ( "brettnr", $bnr );
        $anmeldung->addPlayer ( $spieler );
    }

    // Mannschaft anmelden (bricht bei Fehler ab)
    if ( $anmeldung->saveToDB () )
    {
?>

<b>Vielen Dank f&uuml;r Ihre Anmeldung.</b><br />
<br />

<?php
    // Grundsätzliches
    $confirm = "";
    $confirm .= "Mannschaft: ".$anmeldung->get("name")." ".$anmeldung->get("mnr")."\n";
    $confirm .= SED_utf8_decode("Mannschaftsführer: ").$anmeldung->get("mf_name")."\n";
    $confirm .= "Mannschaftsseite: $globals[httppath]$prefs[directory]/?mannschaft=".$anmeldung->get("id")."\n";
    $confirm .= "\n";
    
    // Aufstellung
    $confirm .= "Aufstellung:\n";
    foreach ( $anmeldung->getPlayerList () as $player )
        $confirm .= $player->getName ()."\n";

    // Ausgabe
    echo nl2br ( $confirm );

    // Anmeldebestätigung
    if ( $prefs ['anmTLMail'] && !$isTurnierleiter )
    {
        // eMail vorbereiten
        require_once ( "mail2.inc.php" );
        $mail = SED_CreateMailer();
        $mail->Subject = "Neue Mannschaftsmeldung: ".$anmeldung->get("name");
        $mail->Body = $confirm;
        SED_SendMail($mail, SED_MAIL_TURNIERLEITER);
    }

    // Turnierleiter? Dann gleich weitere Mannschaft anmelden
    if ( $isTurnierleiter ){
        echo "<meta http-equiv='refresh' content='0;URL=".SED_GenerateFormAction(array("changeteam"))."' />";
        exit;
    }
        
} // endif saveToDB
else {
    return SED_Error ( "Da ist etwas schief gegangen. Wir k&uuml;mmern uns drum.", false, false, true );
}
?>
