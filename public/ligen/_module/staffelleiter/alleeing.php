<?
/* SL-Bereich: Ergebniseingabe
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

    require_once ( "login.inc.php" );

    // Eingabe einbinden
    include ( "../_module/eingabe/eingabe.php" );

  //////////////////////////////////////////////////////////////////
  // ZUSATZ-FUNKTIONEN VERARBEITEN
  //////////////////////////////////////////////////////////////////

    // Spiel verlegen
    if ( isset ( $_POST ['extra_verlegung'] ) )
    {
        $value = "null";
        if ( $_POST ['extra_verlegung_aktiv'] == "1" )
        {
            if ( $_POST ['extra_verlegung_unbekannt'] == "1" )
                $value = "20201224";
            else
            {
                $regexpr = "/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/";
                if ( preg_match ( $regexpr, $_POST ['extra_verlegung_datum'] ) )
                    $value = substr ( $_POST ['extra_verlegung_datum'], 6 ) . substr ( $_POST ['extra_verlegung_datum'], 3, 2 ) . substr ( $_POST ['extra_verlegung_datum'], 0, 2 );
                else
                    SED_Error ( "Das Datum muss das Format TT.MM.JJJJ haben!" );
            }
        }
        if ( mysql_query ( $tmp = "UPDATE paarungen SET termin=$value WHERE id=$_GET[pid] LIMIT 1", $globals ['db'] ) )
        {
            // Erfolgsmeldung
            SED_Cache::clearSpieltag ( $g_paarung ["staffel"], $g_paarung ["runde"] );
            SED_Cache::clearTeam ( 0, SED_Cache::TEAM_SPIELPLAN );
            echo "<br /><br /><b>Die Daten wurden erfolgreich gespeichert!</b>";
            echo "<meta http-equiv='refresh' content='1;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
            exit;
        }
        else
            SED_Error ( "Fehler bei $tmp", true );
    }

    // Nur Bemerkung ändern
    if ( isset ( $_POST ['extra_nurbem'] ) )
    {
      // Daten ändern
      mysql_query ( "UPDATE paarungen SET bemerkung='" . htmlspecialchars ( $_POST ['extra_nurbem_input'], ENT_COMPAT | ENT_HTML401 , 'ISO-8859-1' ) . "' WHERE id=$_GET[pid] LIMIT 1", $globals ['db'] );
      SED_Cache::clearSpieltag ( $g_paarung ["staffel"], $g_paarung ["runde"] );

      // Erfolgsmeldung
      echo "<br /><br /><b>Die Daten wurden erfolgreich gespeichert!</b>";
      echo "<meta http-equiv='refresh' content='1;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
      exit;
    }

    // Alle Ergebnisse löschen
    if ( isset ( $_POST ['extra_delete'] ) )
    {
      // Daten ändern
      mysql_query ( "DELETE FROM spielerpaarungen WHERE paarung=$_GET[pid]", $globals ['db'] );
      mysql_query ( "UPDATE paarungen SET erg1=NULL, erg2=NULL, bemerkung=NULL WHERE id=$_GET[pid] LIMIT 1", $globals ['db'] );
      SED_Cache::clearAll ();

      // Erfolgsmeldung
      echo "<br /><br /><b>Die Daten wurden erfolgreich gespeichert!</b>";
      echo "<meta http-equiv='refresh' content='1;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
      exit;
    }

    // Ausrichter festlegen
    if ( isset ( $_POST ['extra_ausrichter'] ) )
    {
      // Daten ändern - Hinweis: Hier absichtlich keine Fehlerprüfung für value=0
      if ( !is_numeric ( $_POST ['extra_ausrichter_select'] ) )
        $_POST ['extra_ausrichter_select'] = "null";
      mysql_query ( "UPDATE paarungen SET ausrichter=$_POST[extra_ausrichter_select] WHERE id=$_GET[pid] LIMIT 1", $globals ['db'] );
      SED_Cache::clearSpieltag ( $g_paarung ['staffel'], $g_paarung ['runde'] );
      SED_Cache::clearTeam ( 0, SED_Cache::TEAM_SPIELPLAN );

      // Erfolgsmeldung
      echo "<br /><br /><b>Die Daten wurden erfolgreich gespeichert!</b>";
      echo "<meta http-equiv='refresh' content='1;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
      exit;
    }


  //////////////////////////////////////////////////////////////////
  // ZUSATZFUNKTIONEN FÜR DEN STAFFELLEITER
  //////////////////////////////////////////////////////////////////

    // Vorbereitungen für Spielverlegung
    $verlegung = $g_paarung ['termin'] ?: '';
    $tmp = explode ( "-", $verlegung ); // YYYY-MM-DD
    $verlegung_aktiv =  count ( $tmp ) == 3;
    $verlegung_aktiv_checked = $verlegung_aktiv ? "checked='checked'" : "";
    $verlegung_unbekannt = ( $verlegung == "2020-12-24" );
    $verlegung_unbekannt_checked = ($verlegung_aktiv && $verlegung_unbekannt) ? "checked='checked'" : "";
    $verlegung_bekannt_checked = !$verlegung_unbekannt ? "checked='checked'" : "";
    $verlegung_datum = ($verlegung_unbekannt || !$verlegung_aktiv) ? "TT.MM.JJJJ" : "$tmp[2].$tmp[1].$tmp[0]";


    // Vorbereitungen für Ausrichter
    $xwai = "";
    foreach ( $globals ['teams'] as $id=>$name )
      $xwai .= "<option value='$id'>$name</option>";
    $xwai = SED_SelectOption ( $xwai, $g_paarung ['ausrichter'] );

    echo "<form action='".SED_GenerateFormAction()."' method='post' name='zusatzform' style='text-align: left'><div>";
    ?>
      <br /><br /><hr class='sed_hr' /><br /><a name='zusatz'></a><span class='sed_hl2'>Zusatzfunktionen</span><br /><br />
      <b>Spiel verlegen</b><br />
      Nutzen Sie diese Funktion, wenn das Spiel nicht am gleichen Tag stattfindet, wie die restlichen Spiele der Staffel.<br />
      <input type="checkbox" name="extra_verlegung_aktiv" id='e_v_a' value="1" <? echo $verlegung_aktiv_checked; ?>> <label for='e_v_a'>Spielverlegung aktivieren</label><br />
      <div style='margin-left: 30px'>
        <input type="radio" name="extra_verlegung_unbekannt" id='e_v_u1' value="1" onchange="document.getElementById('e_v_a').checked='checked'" <? echo $verlegung_unbekannt_checked; ?>> <label for='e_v_u1'>Auf unbekannten Termin verlegen</label><br />
        <input type="radio" name="extra_verlegung_unbekannt" id='e_v_u2' value="0" onchange="document.getElementById('e_v_a').checked='checked'" <? echo $verlegung_bekannt_checked; ?>> <label for='e_v_u2'>Auf folgenden Termin verlegen:</label>
        <input type='text' id='extra_verlegung_datum' name='extra_verlegung_datum' size='10' value="<? echo $verlegung_datum; ?>" maxlength='10' onchange='document.getElementById("e_v_a").checked="checked";' />
        <input type='button' class='sed_submit' id='extra_verlegung_auswahl' value='...' /><br />
      </div>
      <input type="submit" class="sed_submit" name="extra_verlegung" value="Speichern" /><br /><br /><br />

        <script type="text/javascript"><!--
        <?
          echo "Calendar.setup ( {
                  inputField: 'extra_verlegung_datum',
                  button: 'extra_verlegung_auswahl',
                  ifFormat: '%d.%m.%Y',
                  cache: true
                });";
        ?>
        //-->
        </script>

      <b>Nur Bemerkung setzen</b><br />
      Benutzen Sie diese Funktion, um nur die Bemerkung zu einer Paarung zu setzen, ohne Einzelergebnisse einzugeben.<br /><br />
      <input type='text' name='extra_nurbem_input' value='<? echo $g_paarung['bemerkung']; ?>' size='35' />
      <input type="submit" class="sed_submit" name="extra_nurbem" value="Speichern" /><br /><br /><br />

      <b>Ergebnisse l&ouml;schen</b><br />
      Benutzen Sie diese Funktion, um alle Einzelergebnisse sowie das Gesamtergebnis zu l&ouml;schen. Benutzen Sie diese Funktion beispielsweise, wenn die Paarung zu fr&uuml;h eingegeben wurde und sie in den Anfangszustand zur&uuml;ckversetzt werden soll.<br /><br />
      <input type="submit" class="sed_submit" name="extra_delete" value="Ergebnisse l&ouml;schen" /><br /><br /><br />

      <b>Ausrichter</b><br />
      Hier k&ouml;nnen Sie festlegen, wer die Paarung ausrichtet. Keine Angabe bedeutet, dass die erstgenannte Mannschaft der Ausrichter ist.<br /><br />
      <select name='extra_ausrichter_select'><option value='null'></option><? echo $xwai; ?></select>
      <input type="submit" class="sed_submit" name="extra_ausrichter" value="Speichern" /><br /><br /><br />
    <?

    // Formular-Ende
    echo "</div></form>";

?>
