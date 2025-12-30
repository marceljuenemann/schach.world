<?php
/* SL-Bereich: Staffeleinstellungen
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
    // ID, Name, Beschreibung, Textfeldbreite, Max. Länge, Pflichtfeld
    array ( "spielAufsteiger", "Anzahl der Aufsteiger", "Lassen Sie das Feld leer, um die turnierweite Einstellung zu übernehmen.", 3, 2, false ),
    array ( "spielAbsteiger", "Anzahl der Absteiger", "Lassen Sie das Feld leer, um die turnierweite Einstellung zu übernehmen.", 3, 2, false ),
    array ( "spielAufsteigerRelegation", "Anzahl der Mannschaften, die nur unter Umständen aufsteigen (z.B. Relegation)", "Lassen Sie das Feld leer, um die turnierweite Einstellung zu übernehmen.", 3, 2, false ),
    array ( "spielAbsteigerRelegation", "Anzahl der Mannschaften, die nur unter Umständen absteigen (z.B. Relegation)", "Lassen Sie das Feld leer, um die turnierweite Einstellung zu übernehmen.", 3, 2, false ),
    array ( "hr" ),
    array ( "name", "Staffelname", "Der Name des Staffel darf aus höchstens 30 Zeichen bestehen", 30, 30 , true ),
    array ( "runden", "Rundenzahl", "Die Anzahl der Spieltage Ihrer Staffel. Lassen Sie das Feld leer, um die turnierweite Einstellung zu übernehmen.", 3, 2, false ),
    array ( "brettzahl", "Brettanzahl", "An wie vielen Brettern wird bei Mannschaftskämpfen Ihrer Staffel gespielt? Lassen Sie das Feld leer, um die turnierweite Einstellung zu übernehmen.", 3, 2, false ),
    array ( "sysEingabelinks", "Eingabelinks", "Sollen automatisch Eingabelinks versendet werden? Die Mannschaftsführer erhalten bei Aktivierung automatisch drei Tage vor dem Spieltag eine eMail mit einem Link, über den Sie die Ergebnisse selbstständig eingeben können.", array ( "" => "Turnierweite Einstellung &uuml;bernehmen", "1" => "eMails versenden", "0" => "keine eMails versenden" ), 0, false ),
    array ( "hr" ),
    array ( "showPassNr", "Spielbericht: Spieler-Nummern anzeigen", "Sollen auf dem Spielbericht die Spieler-Nummern angezeigt werden?", array ( "" => "Turnierweite Einstellung &uuml;bernehmen", "1" => "Ja, anzeigen", "0" => "Nein, nicht anzeigen" ), 0, false ),
    array ( "showTabelle", "Spielbericht: Tabelle anzeigen", "Soll auf dem Spielbericht eine Tabelle angezeigt werden?", array ( "" => "Turnierweite Einstellung &uuml;bernehmen", "1" => "Ja, anzeigen", "0" => "Nein, nicht anzeigen" ), 0, false ),
    array ( "showNachmeldungen", "Spielbericht: Nachmeldungen anzeigen", "Sollen auf dem Spielbericht die Nachmeldungen angezeigt werden?", array ( "" => "Turnierweite Einstellung &uuml;bernehmen", "1" => "Ja, anzeigen", "0" => "Nein, nicht anzeigen" ), 0, false ),
    array ( "showSpieltagvorschau", "Spielbericht: Spieltagvorschau", "Soll auf dem Spielbericht eine Vorschau auf den nächsten Spieltag angezeigt werden?", array ( "" => "Turnierweite Einstellung &uuml;bernehmen", "1" => "Ja, anzeigen", "0" => "Nein, nicht anzeigen" ), 0, false )
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
      $params = [];
      for ( $i = 0; $i < count ( $frmMF ); ++$i )
      {
        // Trennlinie?
        if ( $frmMF [$i][0] == "hr" )
          continue;

        // Wert berechnen
        $value = $_POST ["frmManager_" . $frmMF [$i][0]];
        if ( $value == "" )
            $value = null;

        // In MySQL Speichern
        $sql .= ", ".$frmMF [$i][0]."=?";
        $params[] = $value;
      }
      
      // Speichervorgang durchführen
      $sql = "UPDATE staffeln SET ".substr ( $sql, 2 )." WHERE id=? LIMIT 1";
      $params[] = $admin["staffel"];
      if ( !SED_Query ( $sql, $params ) )
        SED_Error ( "Es ist ein Fehler aufgetreten!", true );

  	  // Cache leeren
      SED_Cache::clearAll ( $admin ["staffel"] );
      
      // Erfolgsmeldung
      echo "<b>Die Daten wurden erfolgreich gespeichert</b>";
      echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
      exit;
    }
  }


  // Daten abfragen
  $staffel = SED_Row ( 'SELECT * FROM staffeln WHERE id=?', [$admin['staffel']]);
  $einstellungen = SED_Row ( 'SELECT * FROM viewStaffeln WHERE id=?', [$admin['staffel']]);


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

    // Aktuelle Einstellung ausgeben:
    echo " Aktuelle Einstellung: ".$einstellungen [$frmMF [$i][0]]."<br />";

    // Bisherigen Wert auslesen (in staffeln)
    $value = str_replace ( "'", '"', $staffel [ $frmMF [$i][0] ] );

    // Ist es eine Auswahlbox oder ein Textfeld?
    if ( is_array ( $frmMF [$i][3] ) )
    {
        echo "<select style='margin-bottom: 15px; margin-top: 5px;' name='frmManager_" . $frmMF [$i][0] . "'>";
        foreach ( $frmMF [$i][3] as $v => $n )
        {
            echo "\r\n$value <-> $v\r\n";
            
            $selected = ( strcmp ( $value, $v ) == 0 ? "selected='selected'" : "" );
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

