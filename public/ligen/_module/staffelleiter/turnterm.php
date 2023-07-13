<?
/* SL-Bereich: Termine
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

  // Alle Termine ändern
  if ( isset ( $_POST ['buttonsave'] ) )
  {
    // Neue einfügen
    $regexpr = "/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/";
    for ( $r = 1; $r <= $prefs ['runden']; ++$r )
      if ( isset ( $_POST ["termin$r"] ) && $tmp = $_POST ["termin$r"] )
      {
        if ( preg_match ( $regexpr, $tmp ) )
        {
            // Alte löschen
            if ( !mysql_query ( "DELETE FROM termine WHERE turnier=$globals[tid] and staffel is null and runde=$r", $globals ['db'] ) )
                SED_Error ( "Alte Termine konnten nicht gel&ouml;scht werden!", true );
            if ( !mysql_query ( "INSERT INTO termine SET turnier=$globals[tid], runde=$r, datum='" . substr ( $tmp, 6 ) . substr ( $tmp, 3, 2 ) . substr ( $tmp, 0, 2 ) . "'", $globals ['db'] ) )
                SED_Error ( "Termin $r konnte nicht eingef&uuml;gt werden.", true );
        }
        else
          SED_Error ( "Termin $r hat ung&uuml;ltiges Format", false );
      }

	// Cache leeren
	SED_Cache::clearAll ();
	  
    // Erfolgsmeldung
    echo "<b>Die Daten wurden erfolgreich ge&auml;ndert!</b>";
    echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
  }
?>

<div style='text-align: justify'>
  &Uuml;ber dieses Formular k&ouml;nnen Sie die Spieltermine Ihres Turnieres festlegen.
  Benutzen Sie die Buttons neben den Eingabefeldern, um das Datum aus einem Kalender auszuw&auml;hlen.
  Wenn Sie die Termine manuell eingeben, halten Sie sich an das Format tt.mm.jjjj
  <br /><br />
</div>

<form action='<? echo SED_GenerateFormAction(); ?>' method='post'><div>

  <?
    // Bisherige Termine abfragen
    $termine = array ();
    $rsrc = mysql_query ( "SELECT runde, DATE_FORMAT(datum,'%d.%m.%Y') as datum FROM termine WHERE turnier=$globals[tid] and staffel is null ORDER BY runde", $globals ['db'] );
    if ( $rsrc )
      while ( $tmp = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) )
        $termine [$tmp ['runde']] = $tmp ['datum'];

    // Ausgabe
    for ( $r = 1; $r <= $prefs ['runden']; ++$r )
      echo "Spieltag $r:&nbsp;&nbsp;<input type='text' id='termin$r' name='termin$r' size='10' maxlength='10' value='" . ( isset ( $termine [$r] ) ? $termine [$r] : "" ) . "' /> <input type='button' class='sed_submit' id='button$r' value='...' /><br /><br />";
  ?>

  <input type="submit" class="sed_submit" name="buttonsave" value="Speichern" /> <input type='button' class='sed_submit' value='Abbrechen' onclick="<? echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" />

</div></form>


<br /><br />
<a name='AbwT'></a>
<h2>Abweichende Termine</h2>
<div style='text-align: justify'>
  &Uuml;ber dieses Formular k&ouml;nnen Sie die Termine f&uuml;r bestimmte Staffeln festlegen,
  wenn diese von den obigen abweichen.
  <br /><br />
</div>
<?
  // Einen abweichenden Termin hinzufügen
  $regexpr = "/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/";
  if ( isset ( $_POST ['abw_addsubmit'] ) )
  {
    if ( preg_match ( $regexpr, $_POST ['abw_datum'] ) )
        if ( !mysql_query ( "INSERT INTO termine SET turnier=$globals[tid], staffel=$_POST[abw_staffel], runde=$_POST[abw_runde], datum='" . substr ( $_POST ['abw_datum'], 6 ) . substr ( $_POST ['abw_datum'], 3, 2 ) . substr ( $_POST ['abw_datum'], 0, 2 ) . "'", $globals ['db'] ) )
            SED_Error ( "Termin konnte nicht eingef&uuml;gt werden.", true );
        else
            ;
    else
        SED_Error ( "Der Termin muss das Format TT.MM.JJJJ haben!", false );

    // Cache löschen
	SED_Cache::clearAll ( $_POST ['abw_staffel'] );
    SED_Cache::clearTeam ( 0, SED_Cache::TEAM_SPIELPLAN );
  }

  // Einen abweichenden Termin entfernen
  if ( isset ( $_GET ['abw_del'] ) )
  {
    if ( !mysql_query ( "DELETE FROM termine WHERE id=$_GET[abw_del] and turnier=$globals[tid] LIMIT 1", $globals ['db'] ) )
      SED_Error ( "Termin konnte nicht gel&ouml;scht werden.", true );
  }
?>
<form action='<? echo SED_GenerateFormAction(); ?>#AbwT' method='post'>
<table class='sed_tabelle'>
  <tr><th>Staffel</th><th>Runde</th><th>Datum</th><th></th></tr>
  <?
    // Bisherige Termine abfragen
    $rsrc = mysql_query ( "SELECT id, staffel, runde, DATE_FORMAT(datum,'%d.%m.%Y') as datum FROM termine WHERE turnier=$globals[tid] and staffel IS NOT NULL ORDER BY staffel, runde", $globals ['db'] );
    if ( $rsrc )
      while ( $tmp = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) )
      {
        echo "<tr><td>" . $globals ['staffeln'][$tmp ['staffel']] . "</td><td>$tmp[runde]</td><td>$tmp[datum]</td>";
        echo "<td><a href='?admin=turnterm-$admin[userid]-$admin[session]&abw_del=$tmp[id]#AbwT'>L&ouml;schen</a></td></tr>";
      }
      
    // Formular für neuen Termin
    echo '<tr><td><select name="abw_staffel">';
    foreach ( $globals ['staffeln'] as $id => $name )
    {
        $tmp = ( isset ( $_POST ['abw_staffel'] ) && $_POST ['abw_staffel'] == $id ? "selected='selected'" : "" );
        echo "<option $tmp value='$id'>$name</option>";
    }
    ?>
    </select></td>
    <td><input type="text" name="abw_runde" size="1" value="<? if ( isset ( $_POST ['abw_runde'] ) ) echo $_POST ['abw_runde']+1; ?>" /></td>
    <td><input type='text' id='abw_datum' name='abw_datum' size='10' value="<? if ( isset ( $_POST ['abw_datum'] ) ) echo $_POST ['abw_datum']; ?>" maxlength='10' /> <input type='button' class='sed_submit' id='abw_cal' value='...' /></td>
    <td><input type="submit" class="sed_submit" name="abw_addsubmit" value="Hinzuf&uuml;gen" /></td></tr>
</table></form>


<script type="text/javascript"><!--

<?
  for ( $r = 1; $r <= $prefs ['runden']; ++$r )
    echo "Calendar.setup ( {
            inputField: 'termin$r',
            button: 'button$r',
            ifFormat: '%d.%m.%Y',
            cache: true
          });";
  echo "Calendar.setup ( {
          inputField: 'abw_datum',
          button: 'abw_cal',
          ifFormat: '%d.%m.%Y',
          cache: true
        });";
        
?>

--></script>

