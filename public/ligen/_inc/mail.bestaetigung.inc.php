<?
/* Ergebniseingabe-Bestätigungs-eMail
 *
 * Sendet zu einer gegebenen Paarung eine Bestätigungs-eMail an die
 * betroffenen Personen.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage mail
 */
require_once ( "mail2.inc.php" );

// Bestätigungsmail nach Ergebniseingabe
// m1, m2 sind Ergebnisse eines Aufrufes von Mannschaft (inc/mannschaft)
function SED_Bestaetigungsmail ( $s, $r, $p, $m1, $m2, $geaendert ){
    global $globals;
    global $prefs;

    // Daten über Spieltag-Modul holen
    $spieltag = array ();
    if ( !Spieltag ( $globals ['tid'], $s, $r, $spieltag ) )
        SED_Error ( "Best&auml;tigung konnte nicht gesendet werden! (1)" );

    // Paarungs-Daten holen
    $paarung = false;
    foreach ( $spieltag ['paarungen'] as $paar ){
        if ( $paar ['id'] == $p ){
            $paarung = $paar;
            break;
        }
    }
    if ( !$paarung )
        SED_Error ( "Best&auml;tigung konnte nicht gesendet werden! (2)" );

    // eMail-Objekt erzeugen und Daten zusammenstellen
    $mail = SED_CreateMailer();
    $mail->Subject = "@PAARUNG@ / @STAFFEL@";
    $mail->Body = utf8_decode (
"@ERGEBNISSE@
Falls die obige Ergebnismeldung einen Fehler enthält, können Sie die Ergebnisse über folgenden Link korrigieren: @EINGABELINK@
Sie können sich auch direkt an die Staffelleitung (@STAFFELLEITER@) wenden.

Alle Ergebnisse des Spieltages finden Sie unter @SPIELTAG@

Um einzustellen, wer in Ihrem Verein diese und ähnliche eMails erhalten soll, klicken Sie bitte auf folgenden Link: @MANNSCHAFTSDATEN@" );

    $vars = array();
    $vars["EINGABELINK"] = SED_TINYURL_Paarung ( $p );
    $vars["SPIELTAG"] = "$globals[httppath]$prefs[directory]/?staffel=$s&r=$r";
    $vars["PAARUNG"] = "$paarung[m1] - $paarung[m2]";
    $vars["STAFFEL"] = $spieltag ["staffelname"];
    $vars["STAFFELLEITER"] = "$spieltag[sl_name] ($spieltag[sl_email])";
    if ( $geaendert )
        $mail->Subject = "Aktualisierte Version / ".$mail->Subject;
  	$mail->ClearReplyTos();
    $mail->addReplyTo($spieltag['sl_email'], $spieltag['sl_name']);	

    // Ergebnisse erzeugen
    $erg = "";
    $erg .= "$paarung[erg1]:$paarung[erg2]\t$paarung[m1] - $paarung[m2]\n";
    foreach ( $paarung ['paarungen'] as $row )
      $erg .= "$row[erg1]:$row[erg2]\t$row[s1fullname] - $row[s2fullname]\n";
    if ( strlen ( $paarung ['bemerkung'] ) )
        $erg .= "\nBemerkung: $paarung[bemerkung]\n";
    $vars["ERGEBNISSE"] = $erg;

    // An Mannschaftsführer und Zusatzempfänger
    foreach ( array ( $m1, $m2 ) as $mannschaft )
    {
        // Link zum Ändern der Mannschaftsdaten
        $vars["MANNSCHAFTSDATEN"] = SED_TINYURL_Mannschaftsdaten ( $mannschaft->get('id') );

        // Mannschaftsführer
        SED_SendMail( $mail, $mannschaft->get('mf_email'), $vars );

        // An Zusatzempfänger senden
        $emails = SED_Query( 'SELECT email FROM zusatzempfaenger WHERE mannschaft=? and bestaetigung=1', [$mannschaft->get("id")] )->fetchFirstColumn();
        foreach ($emails as $email) {
          SED_SendMail( $mail, $email, $vars );
        }
    }

    // Für Staffelleiter Nachmeldungen aufbereiten
    $nachmeldungen = "";
    foreach ( $spieltag ['nachmeldungen'] as $spieler ){
        if ( in_array ( $spieler ['mid'], array ( $paarung ['mid1'], $paarung ['mid2'] ) ) ){
            $nachmeldungen .= $globals['teams'][$spieler['mid']].": ";
            $nachmeldungen .= "$spieler[nachname], $spieler[vorname] (ab $spieler[berechtigtAb]. Spieltag)\n";
        }
    }
    if ( $nachmeldungen )
        $vars["NACHMELDUNGEN"] = "Nachmeldungen:\n$nachmeldungen\n";
    else
        $vars["NACHMELDUNGEN"] = "";

    // Staffelleiter Bestätigungsmail
    $mail->Body = utf8_decode (
"@ERGEBNISSE@
@NACHMELDUNGEN@
Falls die obige Ergebnismeldung einen Fehler enthält, können Sie die Ergebnisse im Staffelleitungs-Bereich korrigieren: @SLLOGIN@
Dort können sie unter dem Punkt Spieler auch falsche Nachmeldungen löschen bzw. bearbeiten." );
  
    $vars["SLLOGIN"] = SED_TINYURL_Login ( $spieltag ['staffelid'] );
    SED_SendMail( $mail, $spieltag ['sl_email'], $vars );
}

?>
