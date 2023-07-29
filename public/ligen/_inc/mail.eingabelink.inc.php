<?
/* Eingabelink-eMail
 *
 * Zum Senden von eMails, die Links zur Ergebniseingabe beinhalten.
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
require_once ( "mannschaft.class.php" );

function SED_Eingabelinkmail ( $ausrichter, $paarungen ){
  global $globals;

  // Mannschaftsnamen generieren
  $namen = array ();
  $slname = '';  // Theoretisch könnten dies mehrere sein, aber wir nehmen einfach einen.
  $slmail = '';
  foreach ( $paarungen as $paarung ) {
    foreach ( array ( $ausrichter, $paarung ["mid"], $paarung ["mid2"] ) as $team ){
      if ( isset ( $namen [$team] ) ) continue;
      $tmp = new SED_Mannschaft ( $team );
      $namen [$team] = $tmp->get ( "mannschaftsname" );
    }
    $slname = $paarung['slname'];
    $slmail = $paarung['slmail'];
  }

  // eMail-Objekt erzeugen und Daten zusammenstellen
  $mail = SED_CreateMailer();
  $mail->Subject = "Ergebniseingabe: @SUBJECT@";
  $mail->Body = SED_utf8_decode (
"Hallo @AUSRICHTER@,

um die Spielergebnisse in den Schach-Ergebnisdienst einzutragen, können Sie folgenden Link benutzen:
@LINKS@
Um einzustellen, wer in Ihrem Verein diese und ähnliche eMails erhalten soll, klicken Sie bitte auf folgenden Link: @MANNSCHAFTSDATEN@

Bei Fragen wenden Sie sich bitte an Ihren Staffelleitung (@STAFFELLEITER@)." );
  
  $vars = array();
  $vars["AUSRICHTER"] = $namen [$ausrichter];
  if ( count ( $paarungen ) == 1 )
    $vars["SUBJECT"] = $namen[$paarungen[0]["mid"]]." - ".$namen[$paarungen[0]["mid2"]];
  else
    $vars["SUBJECT"] = count ( $paarungen ) . " Eingabelinks";

  // Link-Liste generieren
  $links = "";
  foreach ( $paarungen as $paarung ){
    $links .= $namen[$paarung["mid"]]." - ".$namen[$paarung["mid2"]].": ".SED_TINYURL_Paarung ( $paarung["paarung"] )."\n";
  }
  $vars["LINKS"] = $links;
  $vars["MANNSCHAFTSDATEN"] = SED_TINYURL_Mannschaftsdaten ( $ausrichter );
  $vars["STAFFELLEITER"] = $slmail;

  // Empfänger-Liste erzeugen
  $to = array ( $paarungen [0]["mf_email"] ); // Ausrichter-Email
  $rsrc = mysql_query ( "SELECT email FROM zusatzempfaenger WHERE mannschaft=$ausrichter and eingabelink=1", $globals ['db'] );
  if ( $rsrc )
    while ( $email = ( mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) )
      $to [] = reset ( $email );

  // Reply-To
  $mail->ClearReplyTos();
  $mail->addReplyTo($slmail, $slname);

  // Senden
  SED_SendMail( $mail, $to, $vars );
}
?>
