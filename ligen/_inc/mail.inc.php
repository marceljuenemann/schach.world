<?
/* eMail Bibliothek
 *
 * In dieser Datei wird die Klasse SED_Mail zur Verfügung
 * gestellt, mit der eine eMail versendet werden kann.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage mail
 */
require_once ( "extern/class.phpmailer.php" );
require_once ( "extern/class.smtp.php" );
require_once ( "spieltag.inc.php" );
require_once ( "tinyurl.inc.php" );

class SED_Mail extends PHPMailer
{
    const MAIL_TURNIERLEITER = "##turnierleiter##";
    var $mail_subject;
    var $mail_text;
    var $vars;

    function __construct ( $type )
    {
$this->mail_subject ['eingabe'] = "@PAARUNG@ / @STAFFEL@";
$this->mail_text ['eingabe'] = utf8_decode (
"@ERGEBNISSE@
Falls die obige Ergebnismeldung einen Fehler enthält, können Sie die Ergebnisse über folgenden Link korrigieren: @EINGABELINK@
Sie können sich auch direkt an Ihren Staffelleiter @STAFFELLEITER@ wenden. Wenn die Ergebnisse korrekt sind, müssen Sie nichts weiter machen.

Alle Ergebnisse des Spieltages finden Sie unter @SPIELTAG@

Um einzustellen, wer in Ihrem Verein diese und ähnliche eMails erhalten soll, klicken Sie bitte auf folgenden Link: @MANNSCHAFTSDATEN@" );

/////////////////////

$this->mail_subject ['eingabe_sl'] = "@PAARUNG@ / @STAFFEL@";
$this->mail_text ['eingabe_sl'] = utf8_decode (
"@ERGEBNISSE@
@NACHMELDUNGEN@
Falls die obige Ergebnismeldung einen Fehler enthält, können Sie die Ergebnisse im Staffelleiter-Bereich korrigieren: @SLLOGIN@
Dort können sie unter dem Punkt Spieler auch falsche Nachmeldungen löschen bzw. bearbeiten." );

/////////////////////

$this->mail_subject ['eingabelink'] = "Ergebniseingabe: @SUBJECT@";
$this->mail_text ['eingabelink'] = utf8_decode (
"Hallo @AUSRICHTER@,

um die Spielergebnisse in den Schach-Ergebnisdienst einzutragen, können Sie folgenden Link benutzen:
@LINKS@
Um einzustellen, wer in Ihrem Verein diese und ähnliche eMails erhalten soll, klicken Sie bitte auf folgenden Link: @MANNSCHAFTSDATEN@

Bei Fragen wenden Sie sich bitte an Ihren Staffelleiter (@STAFFELLEITER@)." );

/////////////////////


        // Konstruktor
        global $globals;
      
        // SMTP
        $this->isSMTP();
        $this->Timeout = 10; // seconds
        SED_SmtpConfig($this);

        // Load template
        if ( $type ){
            $this->Subject = $this->mail_subject [$type];
            $this->Body = $this->mail_text [$type];
        }
    }

    function setVar ( $name, $value )
    {
        $this->vars [$name] = $value;
    }

    function Send ( $to )
    {
        // Vorbereitungen
        global $globals;
        global $prefs;

        // Adressen
        $this->ClearAddresses ();
        if ( is_array ( $to ) )
            foreach ( $to as $adr )
                if ( SED_IsValidEmail ( $adr ) )
                    $this->AddAddress ( $adr );
                else ;
        else
            if ( SED_IsValidEmail ( $to ) )
                $this->AddAddress ( $to );
            elseif ( $to == SED_Mail::MAIL_TURNIERLEITER )
                $this->AddAddress ( reset ( SED_MYSQL_Array ( "SELECT email FROM benutzer WHERE id=$prefs[leiter] LIMIT 1" ) ) );

        // Mailtext mit Signatur
        $oldbody = $this->Body;
        $this->Body .= "\n\n---------\nEmail automatisch generiert vom Schach-Ergebnisdienst.\n";

        // Platzhalter setzen
        if ( is_array ( $this->vars ) )
            foreach ( $this->vars as $name=>$value ){
                $this->Subject = str_replace ( "@$name@", $value, $this->Subject );
                $this->Body = str_replace ( "@$name@", $value, $this->Body );
            }

        try {
            $success = "ERROR";
            if ( parent::Send () ){
                echo "Gesendet.<br />";
                $success = "SENT";
            }
            else
                echo "Ein Fehler beim Senden.<br />";

            // Versand loggen
            $log = "eMail $success - Betreff: ".$this->Subject." An: ";
            $log .= is_array($to) ? implode(",",$to) : $to;
            mysql_query ( "INSERT INTO log SET subject='$log'", $globals ['db'] );
        } catch (phpmailerException $e) {
          echo $e->errorMessage();
        } catch (Exception $e) {
          echo $e->getMessage();
        }

        $this->Body = $oldbody;
    }
}
?>
