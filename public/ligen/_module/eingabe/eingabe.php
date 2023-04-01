<?
/* Ergebniseingabe
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage eingabe
 */

  /*
    Es gibt folgende modul-globale Vaiablen:
        g_m1 []         (SED_Mannschaft-Object)
        g_m2 []         (SED_Mannschaft-Object)
        g_paarung []    (*, isset aus mysql)
        g_leiter []     (id, name, telefon, email aus mysql)
        g_edit []       (ggf. Daten der spielerpaarungen)
        g_vars []       (z.Z. brettzahl)

    Folgende Präfixe können die Spieler IDs haben:
        sID     Spieler
        x       Freigelassen
        nNN,VN  Nachmeldung
   */

  require_once ( "turnier.inc.php" );
  require_once ( "mannschaft.class.php" );
  require_once ( "spieler.class.php" );
  require_once ( "gui.inc.php" );
  require_once ( "auth.inc.php" );
  require_once ( "mail.bestaetigung.inc.php" );
  require_once ( "cache.inc.php" );

  //////////////////////////////////////////////////////////////////
  // ABFRAGEN UND VORBEREITUNGEN
  //////////////////////////////////////////////////////////////////

  // Alle Daten da?
  if ( !isset ( $_GET ['p'] ) )
    SED_Error ( "Der Link scheint fehlerhaft gewesen zu sein. Achten Sie darauf, dass Ihr eMail-Programm den Link nicht in der Mitte umgebrochen hat!", true );

  // Daten über Paarung und Staffelleiter abfragen
  $g_paarung = mysql_fetch_array ( mysql_query ( "SELECT *, erg1 IS NOT NULL as isset FROM paarungen WHERE id=$_GET[p]", $globals ['db'] ), MYSQL_ASSOC );
  $g_leiter = mysql_fetch_array ( mysql_query ( "SELECT b.id, b.name, b.telefon, b.email FROM staffeln INNER JOIN benutzer as b ON b.id=staffeln.leiter WHERE staffeln.id=$g_paarung[staffel] AND staffeln.turnier=$globals[tid]", $globals ['db'] ), MYSQL_ASSOC );

  // Daten über die Mannschaften abfragen
  $g_m1 = new SED_Mannschaft ( $g_paarung ['mannschaft1'] );
  $g_m2 = new SED_Mannschaft ( $g_paarung ['mannschaft2'] );

  // Fehlerüberprüfung
  if ( !$g_paarung || !$g_leiter )
    SED_Error ( "Ausnahmefehler #465", true );

  // Wie viele Bretter gibt es?
  $g_vars ['brettzahl'] = SED_GetBrettzahl ( $g_paarung ['staffel'] );

  // ggf. bisher eingegebene Ergebnisse laden
  $g_edit = array ();
  if ( $g_paarung ['isset'] )
  {
    $result = mysql_query ( "SELECT brett, ergebnis1, ergebnis2, spieler1, spieler2 FROM spielerpaarungen WHERE paarung=$_GET[p] ORDER BY brett", $globals ['db'] );
    while ( $temp = mysql_fetch_array ( $result, MYSQL_ASSOC ) )
      $g_edit [$temp ['brett']] = $temp;
  }


  //////////////////////////////////////////////////////////////////
  // BENUTZER VALIDIERUNG
  //////////////////////////////////////////////////////////////////

    // Staffel- oder Turnierleiter?
    if ( isset ( $_GET ['admin'] ) )
    {
        require_once ( "login.inc.php" );
        if ( $admin ['usertype'] == "s" && $admin ['staffel'] != $g_paarung ['staffel'] )
            SED_Error ( "Ihnen fehlt die Berechtigung für diese Staffel!", true );
    }

    // Sonstige Person?
    else
    {
        // Stimmte der Link?
        if ( $_GET ['auth'] != SED_MD5_PID ( $_GET ['p'] ) )
            SED_Error ( "Sie haben keine Berechtigung!", true );

        // Kann die Paarung noch bearbeitet werden?
        if ( $g_paarung ['festgelegt'] && $g_paarung ['isset'] )
            SED_Error ( "Die Paarung kann nicht mehr bearbeitet werden, da bereits eine Rundmail mit den Ergebnissen verschickt wurde! Bitte wenden Sie sich an Ihren Staffelleiter!", true );
    }
    // HIER EVTL. MAL ANONYME EINGABE


  //////////////////////////////////////////////////////////////////
  // ERGEBNIS SPEICHERN
  //////////////////////////////////////////////////////////////////

  if ( isset ( $_POST ['admin_eingabe'] ) )
  {
      ////////////////////////////////////////////////////////////////
      // ALTE SPIELERPAARUNGEN LÖSCHEN
      ////////////////////////////////////////////////////////////////

      if ( $g_paarung ['isset'] )
        if ( !mysql_query ( "DELETE FROM spielerpaarungen WHERE paarung=$_GET[p]", $globals ['db'] ) )
          SED_Error ( "Fehler beim Löschen der alten Spielerpaarungen.", true );


      ////////////////////////////////////////////////////////////////
      // BEMERKUNGEN & GESAMTERGEBNIS
      ////////////////////////////////////////////////////////////////

      $bemerkung = htmlspecialchars ( $_POST ['bemerkungen'], ENT_COMPAT | ENT_HTML401 , 'ISO-8859-1');
      $bemerkung = ( $bemerkung == "" ? "NULL" : "'$bemerkung'" );
      if ( !mysql_query ( "UPDATE paarungen SET erg1='$_POST[gesheim]', erg2='$_POST[gesgast]', bemerkung=$bemerkung, timestamp=NOW() WHERE id=$_GET[p]", $globals ['db'] ) )
        SED_Error ( "Fehler beim Speichern der Bemerkung und des Gesamtergebnisses!", true );


      ////////////////////////////////////////////////////////////////
      // NACHMELDUNGEN UND ERGEBNISSE SPEICHERN
      ////////////////////////////////////////////////////////////////

      for ( $i = 1; $i <= $g_vars ['brettzahl']; ++$i )
      {
        // Nachmeldungen verarbeiten
        foreach ( array ( "spiheim", "spigast" ) as $team )
        {
            if ( $_POST ["$team$i"][0] == "n" )
            {
                // Nachmeldung erstellen
                $spieler = new SED_Spieler ();
                $spieler->set ( "nmSid", $g_paarung ['staffel'] );
                $spieler->set ( "nmR", $g_paarung ['runde'] );
                $spieler->set ( "mannschaft", ( $team == "spiheim" ? $g_paarung ['mannschaft1'] : $g_paarung ['mannschaft2'] ) );

                // Namen trennen
                try {
                    $spieler->setName ( substr ( $_POST ["$team$i"], 1 ) );
                } catch ( Exception $e ) {
                    SED_Error ( $e->getMessage (), true );
                }

                // Versuchen, DWZ etc. aus der Datenbank zu lesen
                $spieler->autofill ();

                // Versuchen in die Datenbank einzufügen
                try {
                    $spieler->saveToDB ();
                    $_POST ["$team$i"] = "s".$spieler->get("id");
                } catch ( Exception $e ) {
                    SED_Error ( "Nachmeldung konnte nicht eingef&uuml;gt werden: ".$e->getMessage (), true );
                }
            }
        }

        // Spielerpaarung speichern
        $s1 = ( $_POST ["spiheim$i"][0] == "x" ? "NULL" : substr ( $_POST ["spiheim$i"], 1 ) );
        $s2 = ( $_POST ["spigast$i"][0] == "x" ? "NULL" : substr ( $_POST ["spigast$i"], 1 ) );
        mysql_query ( "INSERT INTO spielerpaarungen (paarung,brett,spieler1,spieler2,ergebnis1,ergebnis2) VALUES ($_GET[p], $i, $s1, $s2, '".$_POST ["ergheim$i"]."', '".$_POST ["erggast$i"]."' )", $globals ['db'] );
      }
      SED_Cache::clearAll ( $g_paarung ["staffel"] );
      SED_Cache::clearTeam ( 0, SED_Cache::TEAM_SPIELPLAN );
      SED_Cache::clearTeam ( 0, SED_Cache::TEAM_ERGEBNISSE );

      ////////////////////////////////////////////////////////////////
      // BESTÄTIGUNGS-VERSENDUNG
      ////////////////////////////////////////////////////////////////

        if ( !isset ( $_GET ['admin'] ) || ( isset ( $_POST ['checkbox_sende_bestaetigung'] ) && $_POST ['checkbox_sende_bestaetigung'] == 1 ) )
        {
            SED_Bestaetigungsmail ( $g_paarung ['staffel'], $g_paarung ['runde'], $_GET ['p'], $g_m1, $g_m2, $g_paarung ['isset'] );
        }

      // Erfolgsmeldung
      echo "<b>Die Daten wurden erfolgreich gespeichert und versendet!</b>";
      $link = isset ( $_GET ['admin'] )
        ? "$globals[httppath]$prefs[directory]/?admin=desktop-$admin[userid]-$admin[session]"
        : "$globals[httppath]$prefs[directory]/?staffel=$g_paarung[staffel]&r=$g_paarung[runde]";
      echo "<meta http-equiv='refresh' content='1;URL=$link' />";
  }
  else {


  //////////////////////////////////////////////////////////////////
  // EINGABE-FORMULAR
  //////////////////////////////////////////////////////////////////

  // Überschrift und Formularanfang
  echo "<span class='sed_hl2'>".$g_m1->get("mannschaftsname")." - ".$g_m2->get("mannschaftsname")."</span><br /><br />";
  echo "<form action='".SED_GenerateFormAction()."' method='post' name='eingabeform' style='text-align: left'><div>";

  // Ergebniseingabe
  {
    echo "<table class='sed_normal'><tr><th>Br</th><th>Heim</th><th>Erg</th><th></th><th>Erg</th><th>Gast</th></tr>";

    // Stringerstellung: Einzelergebnis-Auswahl
    $ergs = array ( "0", "½", "1", "+", "-", "?" );
    $ergstr = "";
    foreach ( $ergs as $v )
      $ergstr .= "<option value='$v'>$v</option>";

    // Stringerstellung: Gesamtergebnis-Auswahl
    $gesstr = "";
    for ( $i = 0; $i <= $g_vars ['brettzahl']; $i += 0.5 )
      $gesstr .= "<option value='$i'>".SED_Ergebnis ( $i )."</option>";

    // Stringerstellung: Spieler-Auswahl
    $heimspi = "";
    foreach ( $g_m1->getAufstellung () as $spieler )
        $heimspi .= "<option value='s$spieler[id]'>$spieler[brettnr] ".SED_Spielername($spieler)."</option>";
    $heimspi .= "<option value='x'></option>";
    if ( $prefs ['spielNachmeldungen'] || isset ( $admin ) )
        $heimspi .= "<option value='n'>Nachmeldung...</option>";

    // Stringerstellung: Spieler-Auswahl
    $gastspi = "";
    foreach ( $g_m2->getAufstellung () as $spieler )
        $gastspi .= "<option value='s$spieler[id]'>$spieler[brettnr] ".SED_Spielername($spieler)."</option>";
    $gastspi .= "<option value='x'></option>";
    if ( $prefs ['spielNachmeldungen'] || isset ( $admin ) )
        $gastspi .= "<option value='n'>Nachmeldung...</option>";

    // Ausgabe einer Zeile in der Ergebniseingabe-Tabelle
    for ( $b = 1; $b <= $g_vars ['brettzahl']; ++$b )
    {
      // In temporäre Variablen
      $temp1 = $temp2 = $ergstr;
      $temp3 = $heimspi;
      $temp4 = $gastspi;

      // Ergebnis und Spieler vorauswählen
      if ( $g_paarung ['isset'] )
      {
        $temp1 = SED_SelectOption ( $temp1, $g_edit [$b]['ergebnis1'] );
        $temp2 = SED_SelectOption ( $temp2, $g_edit [$b]['ergebnis2'] );

        // Spieler oder NULL-Spieler?
        if ( $g_edit [$b]['spieler1'] )
          $temp3 = SED_SelectOption ( SED_SelectOption ( $temp3, "s" . $g_edit [$b]['spieler1'] ), "e" . $g_edit [$b]['spieler1'] );
        else
          $temp3 = SED_SelectOption ( $temp3, "x" );
        if ( $g_edit [$b]['spieler2'] )
          $temp4 = SED_SelectOption ( SED_SelectOption ( $temp4, "s" . $g_edit [$b]['spieler2'] ), "e" . $g_edit [$b]['spieler2'] );
        else
          $temp4 = SED_SelectOption ( $temp4, "x" );
      }

      // Ausgabe
      echo "<tr><td>$b.</td>
            <td><select name='spiheim$b' onchange='SED_OnSelectSpieler(\"spiheim\",$b);'>$temp3</select></td>
            <td><select name='ergheim$b' onchange='SED_OnSelectEinzelergebnis(\"ergheim\",$b);'>$temp1</select></td>
            <td>:</td>
            <td><select name='erggast$b' onchange='SED_OnSelectEinzelergebnis(\"erggast\",$b);'>$temp2</select></td>
            <td><select name='spigast$b' onchange='SED_OnSelectSpieler(\"spigast\",$b);'>$temp4</select></td></tr>";
    }

    // Ausgabe der Zeile für das Gesamtergebnis
    $temp1 = $g_paarung ['isset'] ? SED_SelectOption ( $gesstr, $g_paarung ['erg1'] ) : $gesstr;
    $temp2 = $g_paarung ['isset'] ? SED_SelectOption ( $gesstr, $g_paarung ['erg2'] ) : $gesstr;
    echo "<tr><td colspan='2'><b>Gesamtergebnis:</b></td><td><select name='gesheim' onchange='SED_OnSelectGesamtergebnis(\"gesheim\");'>$temp1</select></td><td></td><td><select name='gesgast' onchange='SED_OnSelectGesamtergebnis(\"gesgast\");'>$temp2</select></td></table><br /><br />";
  }

  // Bemerkung
  echo "<b>Öffentlich sichtbare Bemerkung:</b><br /><input type='text' name='bemerkungen' value='$g_paarung[bemerkung]' size='35' /><br /><br />";

  // Abschlusstext (Bei SL.: Bestätigung verschicken?)
  if ( !isset ( $_GET ['admin'] ) )
    echo "<b>Bitte kontrollieren Sie die Eingaben nochmals!</b> Nach einem Klick auf Speichern werden die Eingaben auch per Email an den Staffelleiter und die gegnerische Mannschaft versendet.<br /><br />";
  else
    echo "<input type='checkbox' name='checkbox_sende_bestaetigung' id='checkbox_sende_bestaetigung' value='1' /> <label for='checkbox_sende_bestaetigung'><b>Bestätigung versenden</b> - Wenn Sie das folgende Auswahlfeld aktivieren, wird an die Mannschaftsführer der beiden Mannschaften eine Email mit den Spielergebnissen gesendet.</label><br /><br />";

  // Submit-Button
  echo "<input type='submit' name='admin_eingabe' value='Speichern' class='sed_submit' /> ";
  if ( isset ( $_GET ['admin'] ) )
    echo "<input type='button' class='sed_submit' value='Abbrechen' onclick=\"location='?admin=desktop-$admin[userid]-$admin[session]';\" />";

  // DWZ-Datenbank
  foreach ( $g_m1->getAufstellung () as $spieler  )
    echo "<input type='hidden' name='dwz_s$spieler[id]' value='$spieler[dwz]' />";
  foreach ( $g_m2->getAufstellung () as $spieler  )
    echo "<input type='hidden' name='dwz_s$spieler[id]' value='$spieler[dwz]' />";

  // Scripteinbindung
  if ( $g_paarung ['isset'] )
    echo "<script type='text/javascript' src='$globals[basedir]/_module/eingabe/bearbeitung.js'></script>";
  else
    echo "<script type='text/javascript' src='$globals[basedir]/_module/eingabe/eingabe.js'></script>";

  echo "</div></form>";
}
?>
