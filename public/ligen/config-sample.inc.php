<?php
/* Konfiguration
 *
 * In dieser Datei sind einige Einstellungen vorzunehmen, wie Infos
 * über den Admin, den Server, oder Sicherheitseinstellungen.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage main
 */

  $globals ['systemversion'] = "0.9.0";

  ///////////////////////////////////////////////////////
  // URLs
  ///////////////////////////////////////////////////////

  // Pfad zum Ergebnisdienst
  $globals ['httppath']          = "https://localhost:6464/ligen/";

  // Pfad zu den Icons
  $globals ['systemicons']          = "$globals[basedir]/_templates/systemicons/";

  ///////////////////////////////////////////////////////
  // MYSQL-DATENBANK
  ///////////////////////////////////////////////////////

  // Benutzername und Passwort für Datenbank
  $globals ['dbuser']            = "docker";
  $globals ['dbpw']              = "docker";

  // Datenbankserver
  $globals ['dbhost']            = "127.0.0.1";

  // Datenbankname
  $globals ['dbname']            = "nsv-main";


  ///////////////////////////////////////////////////////
  // SICHERHEIT
  ///////////////////////////////////////////////////////

  // Hier einen zufälligen Text eintragen
  $globals ['salt']               = "qwertzuiopasdfghjklyxcvbnm";

  // Länge des Sicherheits-Strings bei Login-Links
  $globals ['md5-length']         = 8;



  ///////////////////////////////////////////////////////
  // DEBUG EINSTELLUNGEN
  ///////////////////////////////////////////////////////

  // Debuggen? true = Fehler anzeigen, false = Einstellungen aus php.ini
  $globals ['debug']             = true;

  // Welche Fehler anzeigen, wenn $globals ['debug'] = true? Error-Konstanten wie
  // z.B. E_ERROR, E_WARNING, E_ALL oder 0 benutzen! (siehe http://php.net)
  $globals ['debugmode']         = E_ALL;

  ///////////////////////////////////////////////////////
  // WEBMASTER
  ///////////////////////////////////////////////////////

  // Name des Webmasters
  $globals ['webmaster']         = "Marcel Juenemann";

  // Email des Webmasters
  $globals ['webmaster_mail']    = "webmaster@nsv-online.de";

  // Absender-Email
  $globals ['absender_mail']     = "webmaster@nsv-online.de";

  // Masterpasswort
  $globals ['masterpasswort']    = md5("123456");


  ///////////////////////////////////////////////////////
  // DSB-DATENBANK
  ///////////////////////////////////////////////////////

  // Vereins CSV
  $globals ['dsb_db_verein']     = "http://schachbund.de/dwz/db/verein-csv.php?zps=";

  // Spielerkartei nach ZPS
  $globals ['dsb_db_sp_zps']     = "http://www.schachbund.de/spieler.html?zps=";


  ///////////////////////////////////////////////////////
  // SMTP
  ///////////////////////////////////////////////////////

  function SED_SmtpConfig($mail) {
    // For development, I recommend using mailtrap.io
    $mail->Host       = 'sandbox.smtp.mailtrap.io';
    $mail->Port       = 2525;
    $mail->SMTPAuth   = true;
    $mail->Username   = 'YOUR USERNAME';
    $mail->Password   = 'YOUR PASSWORD';
    $mail->setFrom('webmaster@nsv-online.de', 'Schach Ergebnisdienst');
  }
?>
