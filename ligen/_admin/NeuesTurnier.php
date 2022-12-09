<?
/* Neues Turnier anlegen
 * 
 * Zum Anlegen eines neuen Turnieres.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage admin
 */
    require_once ( "admin.inc.php" );
?>

<h1>Neues Turnier</h1>

<form action="" method="post"><div>

<?
  $frmManager = array
  (
    // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
    array ( "adminpasswort", "Admin-Passwort", "Sie benötigen das Admin-Passwort, um ein neues Turnier anzulegen!", 10, 20, true ),
    array ( "organisation", "Welche Organisation richtet das Turnier aus?", "z.B. 7, 701, 701j", 20, 40 , true ),
    array ( "startjahr", "In welchem Jahr startet das Turnier?", "z.B. 2010 f&uuml;r 2010/2011", 20, 20, true ),
    array ( "tname", "Turniername", "Der Name des Turniers sollte aus höchstens 15 Zeichen bestehen", 20, 40 , true ),
    array ( "tdirectory", "Turnierkürzel", "Diese ID gibt an, über welche URL das Turnier erreichbar ist. Die URL zu dem Turnier ist http://ihredomain.de/pfad-zum-ergebnisdienst/ID/", 20, 20, true ),
    array ( "bname", "Turnierleiter: Name", "Bitte hier den Namen des Turnierleiters eintragen", 20, 40, true ),
    array ( "bemail", "Turnierleiter: eMail", "Die Angabe einer korrekten Emailadresse ist elementar für den Ergebnisdienst.", 20, 50, true ),
    array ( "bpasswort", "Turnierleiter: Passwort", "Mit diesem Passwort können Sie sich in den Turnierleiter-Bereich einloggen und alle Einstellungen vornehmen", 10, 20, true )
  );

  if ( isset ( $_POST ['submitter'] ) )
  {
    if ( md5 ( $_POST ['frmManager_adminpasswort'] ) != $globals ['masterpasswort'] )
        SED_Error ( "Admin-Passwort ist falsch!", true );  
      
    // Datenüberprüfung
    $errcount = 0;
    for ( $i = 0; $i < count ( $frmManager ); ++$i )
    {
      // Ist zu lang?
      if ( $frmManager [$i][4] < strlen ( $_POST ['frmManager_' . $frmManager [$i][0]] ) )
      {
        echo "<span style='color:red'>Der Text in Feld " . $frmManager [$i][1] . " ist zu lang!</span><br />";
        ++$errcount;
      }

      // Ist Pflicht und nicht gesetzt?
      if ( $frmManager [$i][5] && strlen ( $_POST ['frmManager_' . $frmManager [$i][0]] ) == 0 )
      {
        echo "<span style='color:red'>Das Feld " . $frmManager [$i][1] . " ist ein Pflichtfeld!</span><br />";
        ++$errcount;
      }
    }

    // ggf. Turnier wirklich anlegen
    if ( $errcount == 0 )
    {
        // MySQL Benutzer Datensatz anlegen
        mysql_query ( "INSERT INTO benutzer SET name='$_POST[frmManager_bname]', passwort=MD5('$_POST[frmManager_bpasswort]'), email='$_POST[frmManager_bemail]'", $globals ['db'] );
        mysql_query ( "INSERT INTO turniere SET leiter='".mysql_insert_id()."', name='$_POST[frmManager_tname]', directory='$_POST[frmManager_tdirectory]', template='sjbh', organisation='$_POST[frmManager_organisation]', startjahr='$_POST[frmManager_startjahr]', anmVerband='$_POST[frmManager_organisation]'", $globals ['db'] ); 
        
        // Anzeigen
        echo "<meta http-equiv='refresh' content='0;URL=$globals[basedir]/$_POST[frmManager_tdirectory]/' />";
        exit;
    }

    echo "<br />";
  }

  // Formularausgabe
  for ( $i = 0; $i < count ( $frmManager ); ++$i )
  {
    echo "<b>" . $frmManager [$i][1] . " " . ( $frmManager [$i][5] ? "*" : "" ) . "</b><br />";
    echo $frmManager [$i][2] . "<br />";
    $value = isset ( $_POST ['frmManager_' . $frmManager [$i][0]] ) ? $_POST ['frmManager_' . $frmManager [$i][0]] : "";
    $type = ( $frmManager [$i][0] == "bpasswort" || $frmManager [$i][0] == "adminpasswort" ) ? "password" : "text";

    echo "<input type='$type' name='frmManager_" . $frmManager [$i][0] . "' value='$value' size='" . $frmManager [$i][3] . "' maxlength='" . $frmManager [$i][4] . "' /><br /><br />";
  }
?>

<input type="submit" name="submitter" value="Erstellen" />

</div></form>

