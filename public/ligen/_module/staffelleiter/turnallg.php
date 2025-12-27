<?php
/* SL-Bereich: Turniereinstellungen
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

  $frmMF = array
  (
    // TEXT: ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
    // SELECT: ID, Name, Beschreibung, Möglichkeiten, 0, false
    array ( "name", "Turniername", "Der Name des Turniers sollte aus höchstens 15 Zeichen bestehen", 20, 40 , true ),
    array ( "runden", "Rundenzahl", "Die Anzahl der Spieltage Ihres Turnieres. Sollten die Staffeln unterschiedliche Rundenzahlen haben, geben Sie bitte die größte Zahl an.", 3, 2, true ),
    array ( "brettzahl", "Brettanzahl", "An wie vielen Brettern wird bei Mannschaftskämpfen Ihres Turnieres gespielt?", 3, 2, true ),
    array ( "hr" ),
    array ( "sysEingabelinks", "Eingabelinks", "Sollen automatisch Eingabelinks versendet werden? Die Mannschaftsführer erhalten bei Aktivierung automatisch drei Tage vor dem Spieltag eine eMail mit einem Link, über den Sie die Ergebnisse selbstständig eingeben können.", array ( 1 => "eMails versenden", 0 => "keine eMails versenden" ), 0, false ),
    array ( "spielErsatzmannschaft", "Ersatzmannschaften", "Sind Spieler aus tieferen Mannschaften des gleichen Vereins automatisch Ersatzspieler?", array ( 0 => "Nein, keine Ersatzmannschaften", 1 => "Ja, aber nur die n&auml;chsttiefere", 99 => "Ja, alle tieferen Mannschaften" ), 0, false ),
    array ( "spielNachmeldungen", "Nachmeldungen", "Dürfen Mannschaftsführer bei der Ergebniseingabe Spieler nachmelden?", array ( 0 => "Nein, keine Nachmeldungen am Spieltag", 1 => "Ja, Nachmeldungen zulassen" ), 0, false ),
    array ( "spielDreistelligeNr", "Spieler-Nummern", "In welchem Format sollen die Spielernummern angezeigt werden?", array ( 1 => "dreistellig (erste Ziffer ist Mannschaftsnummer)", 0 => "einstellig" ), 0, false ),
    array ( "hr" ),
    array ( "spielAufsteiger", "Anzahl der Aufsteiger pro Staffel", "Sollte die Anzahl der Mannschaften in einzelnen Staffeln abweichen, so können Sie das in den Staffeleinstellungen ändern.", 3, 2, false ),
    array ( "spielAbsteiger", "Anzahl der Absteiger pro Staffel", "Sollte die Anzahl der Mannschaften in einzelnen Staffeln abweichen, so können Sie das in den Staffeleinstellungen ändern.", 3, 2, false ),
    array ( "spielAufsteigerRelegation", "Anzahl der Mannschaften, die nur unter Umständen aufsteigen (z.B. Relegation)", "Sollte die Anzahl der Mannschaften in einzelnen Staffeln abweichen, so können Sie das in den Staffeleinstellungen ändern.", 3, 2, false ),
    array ( "spielAbsteigerRelegation", "Anzahl der Mannschaften, die nur unter Umständen absteigen (z.B. Relegation)", "Sollte die Anzahl der Mannschaften in einzelnen Staffeln abweichen, so können Sie das in den Staffeleinstellungen ändern.", 3, 2, false ),
    array ( "hr" ),
    array ( "showPassNr", "Spielbericht: Spieler-Nummern anzeigen", "Sollen auf dem Spielbericht die Spieler-Nummern angezeigt werden?", array ( 1 => "Ja, anzeigen", 0 => "Nein, nicht anzeigen" ), 0, false ),
    array ( "showTabelle", "Spielbericht: Tabelle anzeigen", "Soll auf dem Spielbericht eine Tabelle angezeigt werden?", array ( 1 => "Ja, anzeigen", 0 => "Nein, nicht anzeigen" ), 0, false ),
    array ( "showNachmeldungen", "Spielbericht: Nachmeldungen anzeigen", "Sollen auf dem Spielbericht die Nachmeldungen angezeigt werden?", array ( 1 => "Ja, anzeigen", 0 => "Nein, nicht anzeigen" ), 0, false ),
    array ( "showSpieltagvorschau", "Spielbericht: Spieltagvorschau", "Soll auf dem Spielbericht eine Vorschau auf den nächsten Spieltag angezeigt werden?", array ( 1 => "Ja, anzeigen", 0 => "Nein, nicht anzeigen" ), 0, false ),
    array ( "hr" ),
    array ( "infomeldung", "Hinweis-Meldung", "Hier können Sie eine Meldung festlegen, die auf jeder Seite (öffentlich) gut sichtbar angezeigt wird. HTML für Links ist erlaubt.", 60, 200, false )
  );
  foreach ($frmMF as &$field) {
    if (count($field) < 3) continue;
    $field[1] = SED_utf8_decode($field[1]);
    $field[2] = SED_utf8_decode($field[2]);
  }

  // Speichern
  if ( isset ( $_POST ['savebutton'] ) )
  {
    // Datenprüfung
    $errors = array ();
    for ( $i = 0; $i < count ( $frmMF ); ++$i )
    {
      // Ist es eine Trennlinie oder Auswahlliste?
      if ( !isset ( $frmMF [$i][3] ) || is_array ( $frmMF [$i][3] ) )
        continue;

      // Ist zu lang?
      if ( $frmMF [$i][4] < strlen ( $_POST ['frmManager_' . $frmMF [$i][0]] ) )
        $errors [] = "Der Text in Feld " . $frmMF [$i][1] . " ist zu lang!";

      // Ist Pflicht und nicht gesetzt?
      if ( $frmMF [$i][5] && strlen ( $_POST ['frmManager_' . $frmMF [$i][0]] ) == 0 )
        $errors [] = "Das Feld " . $frmMF [$i][1] . " ist ein Pflichtfeld!";
    }

    // Fehlerausgabe
    if ( count ( $errors ) > 0 )
    {
      foreach ( $errors as $error )
        echo "<span style='color: red; font-weight: bold'>$error</span><br />";
      echo "<br />";
    }

    if ( !count ( $errors ) )
    {
      // Speichervorgang vorbereiten
      $sql = "";
      $values = [];
      for ( $i = 0; $i < count ( $frmMF ); ++$i )
      {
        // Trennlinie?
        if ( $frmMF [$i][0] == "hr" )
          continue;
          
        // In $prefs speichern
        $prefs [ $frmMF [$i][0] ] = $_POST ["frmManager_" . $frmMF [$i][0]];

        // In MySQL Speichern
        $sql .= ", ".$frmMF [$i][0]."=?";
        $values[] = $_POST ["frmManager_" . $frmMF [$i][0]];
      }
      
      // Speichervorgang durchführen
      $sql = "UPDATE turniere SET ".substr ( $sql, 2 )." WHERE id=? LIMIT 1";
      $values[] = $globals['tid'];
      if ( !SED_TryQuery ( $sql, $values ) )
        SED_Error ( "Es ist ein Fehler aufgetreten!", true );

	  // Cache leeren
	  SED_Cache::clearAll ();
	  
      // Erfolgsmeldung
      echo "<b>Die Daten wurden erfolgreich gespeichert</b>";
      echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
      exit;
    }
  }


  // Felder ausgeben
  // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
  echo "<form action='".SED_GenerateFormAction()."' method='post'><div>";
  for ( $i = 0; $i < count ( $frmMF ); ++$i )
  {
    // Ist dies nur eine Trennlinie?
    if ( $frmMF [$i][0] == "hr" )
    {
        echo "<hr class='sed_hr' /><br />";
        continue;
    }

    // Text ausgeben
    echo "<span style='font-weight: bold'>" . $frmMF [$i][1] . " " . ( $frmMF [$i][5] ? "*" : "" ) . "</span><br />";
    echo $frmMF [$i][2] . "<br />";

    // Bisherigen Wert auslesen
    $value = str_replace ( "'", '"', $prefs [ $frmMF [$i][0] ] );

    // Ist es eine Auswahlbox oder ein Textfeld?
    if ( is_array ( $frmMF [$i][3] ) )
    {
        echo "<select style='margin-bottom: 15px; margin-top: 5px;' name='frmManager_" . $frmMF [$i][0] . "'>";
        foreach ( $frmMF [$i][3] as $v => $n )
        {
            $selected = ( $value == $v ? "selected='selected'" : "" );
            echo "<option $selected value='$v'>$n</option>";
        }
        echo "</select><br />";
    }
    else
        echo "<input type='text' style='margin-bottom: 15px; margin-top: 5px;' name='frmManager_" . $frmMF [$i][0] . "' value='$value' size='" . $frmMF [$i][3] . "' maxlength='" . $frmMF [$i][4] . "' /><br />";
  }

?>

  <input type='submit' name='savebutton' class='sed_submit' value='Speichern' />
  <input type='button' class='sed_submit' value='Abbrechen' onclick="location=<?php echo "'?admin=desktop-$admin[userid]-$admin[session]'"; ?>;" />
</div></form>

