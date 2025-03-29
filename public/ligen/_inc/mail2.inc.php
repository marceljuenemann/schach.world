<?
require_once ( "../../libs/phpmailer/class.phpmailer.php" );
require_once ( "../../libs/phpmailer/class.smtp.php" );
require_once ( "spieltag.inc.php" );
require_once ( "tinyurl.inc.php" );

const SED_MAIL_TURNIERLEITER = "##turnierleiter##";

function SED_CreateMailer() {
  $mail = new PHPMailer();
  $mail->isSMTP();
  $mail->Timeout = 10; // seconds
  SED_SmtpConfig($mail);
  return $mail;  
}

function SED_SendMail($mailer, $to, $vars = array()) {
  global $globals;
  global $prefs;

  // Adressen
  $mailer->ClearAddresses ();
  if ( is_array ( $to ) ) {
      foreach ( $to as $adr ) {
          if ( SED_IsValidEmail ( $adr ) ) {
              $mailer->AddAddress ( $adr );
          }
      }
  } else {
      if ( SED_IsValidEmail ( $to ) ) {
          $mailer->AddAddress ( $to );
      } elseif ( $to == SED_MAIL_TURNIERLEITER ) {
         $mailer->AddAddress ( SED_Value ( "SELECT email FROM benutzer WHERE id=? LIMIT 1", [$prefs['leiter']] ) );
      } 
  }

  // Mailtext mit Signatur
  $oldbody = $mailer->Body;
  $mailer->Body .= "\n\n---------\nEmail automatisch generiert vom Schach-Ergebnisdienst.";

  // Platzhalter setzen
  if ( is_array ( $vars ) ) {
      foreach ( $vars as $name=>$value ){
          $mailer->Subject = str_replace ( "@$name@", $value, $mailer->Subject );
          $mailer->Body = str_replace ( "@$name@", $value, $mailer->Body );
      }
  }

  try {
      $success = "ERROR";
      if ( $mailer->Send () ){
          echo "Gesendet.<br />";
          $success = "SENT";
      }
      else {
          echo "Ein Fehler beim Senden.<br />";
          echo "<!-- " . $mailer->ErrorInfo . " -->";
      }

      // Versand loggen
      $log = "eMail $success - Betreff: ".$mailer->Subject." An: ";
      $log .= is_array( $to ) ? implode( ",", $to ) : $to;
      SED_Query ( "INSERT INTO log SET subject=?", [$log] );
  } catch (phpmailerException $e) {
    echo $e->errorMessage();
  } catch (Exception $e) {
    echo $e->getMessage();
  }

  $mailer->Body = $oldbody;
}
?>
