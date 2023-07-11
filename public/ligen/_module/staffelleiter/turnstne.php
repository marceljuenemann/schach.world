<?
TODO
/* SL-Bereich: Neue Staffel
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

    require_once ( "login.inc.php");
    require_once ( "ajax.inc.php" );

  $frmStaffel = array (
    // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
    array ( "xname", "Name der Staffel", "Beispiele für Staffelnamen: Staffel Ost, Kreisliga Süd-West, Gruppe A", 30, 40, true ),
    array ( "name", "Name des Staffelleiters", "Der vollständige Name des Staffelleiters.", 30, 40, true ),
    array ( "email", "Emailadresse", "Die Emailadresse, über die wichtige Informationen verschickt werden. Bitte geben Sie eine Adresse an, die regelmäßig abgerufen wird.", 30, 50, true ),
    array ( "email2", "Email wiederholen", "Um sicherzugehen, dass die eingegebene Adresse keine Tippfehler enthält, wiederholen Sie die Adresse bitte.", 30, 50, true ),
    array ( "telefon", "Telefon", "Die Telefonnummer des Staffelleiters, über die er von Mannschaften kontaktiert werden kann.", 15, 30, true ),
    array ( "telefon2", "Telefon alternativ", "Eine zweite Telefonnummer, wie z.B. Mobiltelefon. Dieses Feld ist optional", 15, 30, false ),
    array ( "passwort", "Passwort", "Das Anfangs-Passwort für den Staffelleiter", 10, 35, true )
  );

  // Überprüfung
  if ( isset ( $_POST ['neue_staffel'] ) )
  {
    // Staffelleiter überprüfen und einfügen
    $errors = array ();
    for ( $i = 0; $i < count ( $frmStaffel ); ++$i )
    {
      // Ist zu lang?
      if ( $frmStaffel [$i][4] < strlen ( $_POST ['frmManager_' . $frmStaffel [$i][0]] ) )
        $errors [] = "Der Text in Feld " . $frmStaffel [$i][1] . " ist zu lang!";

      // Ist Pflicht und nicht gesetzt?
      if ( $frmStaffel [$i][5] && strlen ( $_POST ['frmManager_' . $frmStaffel [$i][0]] ) == 0 )
        $errors [] = "Das Feld " . $frmStaffel [$i][1] . " ist ein Pflichtfeld!";
    }

    // Email
    if ( !SED_IsValidEmail ( $_POST ['frmManager_email'] ) )
      $errors [] = "Bitte geben Sie eine g&uuml;ltige Emailadresse an!";

    // Email Sicherheitswiederholung
    if ( $_POST ['frmManager_email'] != $_POST ['frmManager_email2'] )
      $errors [] = "Die Email Wiederholung stimmte nicht mit der ersten Eingabe &uuml;berein.";

    // Fehlerausgabe
    if ( count ( $errors ) > 0 )
    {
      foreach ( $errors as $error )
        echo "<span style='color: red; font-weight: bold'>$error</span><br />";
      echo "<br />";
    }

    // Erstellen
    else
    {
      // Datenbank
      if ( !mysql_query ( "INSERT INTO benutzer SET name='$_POST[frmManager_name]', passwort=MD5('$_POST[frmManager_passwort]'), random='1234', telefon='$_POST[frmManager_telefon]', telefon2='$_POST[frmManager_telefon2]', email='$_POST[frmManager_email]'", $globals ['db'] ) )
        SED_Error ( "Fehler beim Einf&uuml;gen in die Datenbank! x=0", true );
      if ( !mysql_query ( "INSERT INTO staffeln SET leiter=" . mysql_insert_id ( $globals ['db'] ) . ", name='$_POST[frmManager_xname]', turnier=$globals[tid]", $globals ['db'] ) )
        SED_Error ( "Fehler beim Einf&uuml;gen in die Datenbank! x=1", true );
      else
        // Metatag Refresh
        echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
    }
  }

  // Ausgabe
  echo "<form action='".SED_GenerateFormAction()."' method='post'><div>";
  for ( $i = 0; $i < count ( $frmStaffel ); ++$i )
  {
    echo "<span style='font-weight: bold'>" . $frmStaffel [$i][1] . " " . ( $frmStaffel [$i][5] ? "*" : "" ) . "</span><br />";
    echo $frmStaffel [$i][2] . "<br />";
    $value = isset ( $_POST ['frmManager_' . $frmStaffel [$i][0]] ) ? $_POST ['frmManager_' . $frmStaffel [$i][0]] : "";
    $type = $frmStaffel [$i][0] == "passwort" ? "password" : "text";
    $jscript = ( $i == 1 ? "onchange='GetSlData(this);'" : "" ); 
    echo "<input $jscript type='$type' style='margin-bottom: 15px; margin-top: 5px;' name='frmManager_" . $frmStaffel [$i][0] . "' value='$value' size='" . $frmStaffel [$i][3] . "' maxlength='" . $frmStaffel [$i][4] . "' /><br />";
  }
  
  // Javascript für Datenabfrage
    ?>
    <script type='text/javascript'><!--

      function GetSlData ( obj )
      {
        // Daten abfragen, wenn der Name ok ist
        if ( obj.value.length > 5 )
        {
            <?
                $ajax = new SED_AjaxRequest ( "getMF" );
                $ajax->setOption ( "name", "obj.value" );
                $ajax->onResult ( '
                    // Antwort spliten
                    var data = req.responseText.split ( ";" );
                    
                    // Daten nur setzen, wenn die jeweiligen Felder noch leer sind
                    if ( document.getElementsByName ( "frmManager_telefon" ) [0].value.length < 3 )
                        document.getElementsByName ( "frmManager_telefon" ) [0].value = data [0];
                    if ( document.getElementsByName ( "frmManager_telefon2" ) [0].value.length < 3 )
                        document.getElementsByName ( "frmManager_telefon2" ) [0].value = data [1];
                    if ( document.getElementsByName ( "frmManager_email" ) [0].value.length < 3 )
                        document.getElementsByName ( "frmManager_email" ) [0].value = data [2];
                    if ( document.getElementsByName ( "frmManager_email2" ) [0].value.length < 3 )
                        document.getElementsByName ( "frmManager_email2" ) [0].value = data [2];
                ' );
                echo $ajax->getJavascript ();
            ?>
        }
      }

    --></script>

  <input type='submit' class='sed_submit' name='neue_staffel' value='Absenden' />
  <input type='button' class='sed_submit' value='Abbrechen' onclick="<? echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" />
  </div></form>
