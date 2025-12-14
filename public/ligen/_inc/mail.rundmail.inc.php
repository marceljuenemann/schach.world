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
        $this->infos = SED_Row('SELECT s.name as staffel, b.name, b.email, b.telefon FROM staffeln as s INNER JOIN benutzer as b ON s.leiter=b.id WHERE s.id=? LIMIT 1', [$staffel]);

        // MySQL-Abfragen: Mannschaftsführer, Zusatzempfänger
        $this->mannschaften = SED_Query ( 'SELECT id, mf_name name, mf_email email FROM mannschaften as m WHERE m.staffel=? ORDER BY m.name', [$staffel] )->fetchAllAssociative();
        $this->zusatzempfaenger = SED_Query ( 'SELECT z.mannschaft, z.email FROM mannschaften m INNER JOIN zusatzempfaenger z ON z.mannschaft=m.id WHERE m.staffel=? and z.rundmail=1', [$staffel])->fetchAllAssociative();

        // Rundenzahl
        $this->rundenzahl = SED_GetRundenzahl ( $staffel );
    }

    function getRundenzahl (){
        return $this->rundenzahl;
    }

    function getDefaultText (){
        global $globals; global $prefs;
        return "Liebe Schachfreund:innen,\n\nim Anhang findet ihr die Ergebnisse des ".$this->runde.". Spieltages als PDF. Die Ergebnisse sind auch online verf&uuml;gbar auf $globals[httppath]$prefs[directory]?staffel=".$this->staffel."&r=\n\nMit freundlichen Gr&uuml;&szlig;en\n".$this->infos['name']."\nStaffelleiter\nTel.: ".$this->infos["telefon"]."\n";
    }

    function getDefaultSubject (){
        return $this->infos["staffel"] . " / Rundmail";
    }

    function getTo (){
        $result = array ();

        // Mannschaften & Zusatzemfänger.
        foreach ($this->mannschaften as $team) {
          if ( SED_IsValidEmail ( $team ['email'] ) ) {
            $result [$team ['id']][] = $team ['email'];
          }
        }
        foreach ($this->zusatzempfaenger as $empf) {
          if ( SED_IsValidEmail ( $empf ['email'] ) ) {
            $result [$empf ['mannschaft']][] = $empf ['email'];
          }
        }

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
        if ( $_POST ['runde'] ) {
            $url = "$globals[httppath]$prefs[directory]/Rundschreiben/".$this->staffel."/Runde".$this->runde.".pdf";
            $mail->AddStringAttachment ( file_get_contents ( $url ), "Ergebnisse.pdf" );
        }

        // An Staffelleiter senden
        SED_SendMail( $mail, $this->infos ["email"], array() );

        foreach ( $this->getTo () as $mannschaft=>$to ){
            // An Mannschaften senden
            $sig = "\n\nUm einzustellen, wer in Ihrem Verein diese und andere eMails erhalten soll, klicken Sie bitte auf folgenden Link: ";
            $sig .= SED_TINYURL_Mannschaftsdaten ( $mannschaft );
            $mail->Body = $body.$sig;
            SED_SendMail( $mail, $to, array() );
        }
    }
}
?>
