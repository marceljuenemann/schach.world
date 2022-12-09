<?
/* Rundmail
 *
 * Zum Versenden von Rundmails innerhalb der Staffel.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage libs
 */
    require_once ( "mail2.inc.php" );
    require_once ( "turnier.inc.php" );

class SED_Rundmail {

    function __construct ( $staffel, $runde ){
        // Einstellungen merken
        global $globals;
        $this->staffel = $staffel;
        $this->runde = $runde;

        // MySQL-Abfragen: Staffelinformationen, Aktuelle Runde
        $this->infos = mysql_fetch_array ( mysql_query ( "SELECT s.name as staffel, b.name, b.email, b.telefon FROM staffeln as s INNER JOIN benutzer as b ON s.leiter=b.id WHERE s.id=$staffel LIMIT 1", $globals ['db'] ), MYSQL_ASSOC );

        // MySQL-Abfragen: Mannschaftsführer, Newsletter-Empfänger, Zusatzempfänger
        $this->mannschaften = mysql_query ( "SELECT id, mf_name name, mf_email email FROM mannschaften as m WHERE m.staffel=$staffel ORDER BY m.name", $globals ['db'] );
        $this->zusatzempfaenger = mysql_query ( "SELECT z.mannschaft, z.email FROM mannschaften m INNER JOIN zusatzempfaenger z ON z.mannschaft=m.id WHERE m.staffel=$staffel and z.rundmail=1", $globals ['db'] );
        $this->newsletter = mysql_query ( "SELECT id, random, email FROM rundmail WHERE staffel=$staffel AND aktiv=1", $globals ['db'] );

        // Rundenzahl
        $this->rundenzahl = SED_GetRundenzahl ( $staffel );
    }

    function getRundenzahl (){
        return $this->rundenzahl;
    }

    function getDefaultText (){
        global $globals; global $prefs;
        return "Liebe Schachfreunde,\n\nim Anhang findet ihr die Ergebnisse des ".$this->runde.". Spieltages als PDF. Die Ergebnisse sind auch online verf&uuml;gbar auf $globals[httppath]$prefs[directory]?staffel=".$this->staffel."&r=\n\nMit freundlichen Gr&uuml;&szlig;en\n".$this->infos['name']."\nStaffelleiter\n\nTel.: ".$this->infos["telefon"]."\nMail: ".$this->infos["email"]."\n";
    }

    function getDefaultSubject (){
        return $this->infos["staffel"] . " / Rundmail";
    }

    function getTo (){
        $result = array ();

        // Mannschaften
        if ( $this->mannschaften )
            while ( $team = mysql_fetch_array ( $this->mannschaften ) ){
                if ( SED_IsValidEmail ( $team ['email'] ) )
                    if ( !isset ( $result [$team ['id']] ) )
                        $result [$team ['id']] = array ( $team ['email'] );
                    else
                        $result [$team ['id']][] = $team ['email'];
            }

        // Zusatzempfänger
        if ( $this->zusatzempfaenger )
            while ( $zusatz = mysql_fetch_array ( $this->zusatzempfaenger ) )
                if ( !isset ( $result [$zusatz ['mannschaft']] ) )
                    $result [$zusatz ['mannschaft']] = array ( $zusatz ['email'] );
                else
                    $result [$zusatz ['mannschaft']][] = $zusatz ['email'];

        // Newsletter
        $result ['newsletter'] = array ();
        if ( $this->newsletter )
            while ( $nl = mysql_fetch_array ( $this->newsletter ) )
                $result ['newsletter'][] = $nl;

        return $result;
    }

    function Send ( $subject, $body ){
        global $globals;
        global $prefs;

        // eMail vorbereiten
        $mail = SED_CreateMailer();
        $mail->AddReplyTo ( $this->infos ['email'], $this->infos ['name'] );
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Anhang
        if ( $_POST ['runde'] )
          $mail->AddStringAttachment ( file_get_contents ( "$globals[httppath]$prefs[directory]/index.php?staffel=".$this->staffel."&r=".$this->runde."&ausgabe=pdf" ), "Ergebnisse.pdf" );

        // An Staffelleiter senden
        SED_SendMail( $mail, $this->infos ["email"], array() );

        foreach ( $this->getTo () as $mannschaft=>$to ){
            if ( $mannschaft != "newsletter" ){
                // An Mannschaften senden
                $sig = "\n\nUm einzustellen, wer in Ihrem Verein diese und andere eMails erhalten soll, klicken Sie bitte auf folgenden Link: ";
                $sig .= SED_TINYURL_Mannschaftsdaten ( $mannschaft );
                $mail->Body = $body.$sig;
                SED_SendMail( $mail, $to, array() );
            } else {
                // An Newsletter senden
                foreach ( $to as $nl ){
                    $mail->Body = $body . "\n\n----------------------\nUm in Zukunft keine Rundschreiben mehr zu erhalten, klicken Sie auf folgenden Link: $globals[httppath]$prefs[directory]/?m=newsletter&validate=$nl[id]&remove=true&rnd=$nl[random]";
                    SED_SendMail( $mail, $nl ['email'], array() );
                }
            }
        }
    }
}
?>
