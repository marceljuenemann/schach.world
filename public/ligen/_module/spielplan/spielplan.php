<?
// DEPRECATED - This module was moved to Symfony and this file will be deleted soon.

/* Spielplan
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage frontend
 */

  require_once ( "turnier.inc.php" );
  require_once ( "gui.inc.php" );

  // Wurde das Turnier z.B. umbenannt?
  if ( !isset ( $globals ['staffeln'][$_GET ['staffel']] ) )
  {
    $tname = reset ( mysql_fetch_array ( mysql_query ( "SELECT t.directory FROM staffeln s INNER JOIN turniere t ON t.id=s.turnier WHERE s.id='$_GET[staffel]'", $globals ['db'] ), MYSQL_ASSOC ) );
    echo "<b>Sie werden weitergeleitet...</b>";
    echo "<meta http-equiv='refresh' content='0;URL=$globals[httppath]$tname/?staffel=$_GET[staffel]&r=spielplan' />";
    exit;
  }

  // Überschrift
  echo "<span class='sed_hl1'>Spielplan " . $globals ['staffeln'][$_GET ['staffel']] . "</span><br /><br />";
  echo "<table class='sed_normal' cellspacing='0' cellpadding='2'>";

  // Bei allen Spielen den Ausrichter anzeigen?
  // $res = mysql_query ( "SELECT id FROM paarungen WHERE staffel='$_GET[staffel]' AND ausrichter IS NULL LIMIT 1", $globals['db']);
  // $showAusrichter = (mysql_num_rows($res) === 0);
  
  // Abfragen
  $res = mysql_query ( "SELECT paarungen.runde, mannschaft1, mannschaft2, erg1, erg2, erg1 IS NOT NULL AND erg2 IS NOT NULL as isset, IF(termin IS NULL,'',IF( DATE_FORMAT(termin,'%d%m')='2412','(verlegt)',DATE_FORMAT(termin,'(am %d.%m.)'))) as terminAbw, ausrichter FROM paarungen WHERE staffel=$_GET[staffel] ORDER BY paarungen.runde, paarungen.ausrichter, paarungen.id", $globals ['db'] );
  if ( $res && mysql_num_rows ( $res ) )
  {
      // Rundenzahl berechnen
      // mysql_data_seek ( $res, mysql_num_rows ( $res ) - 1 );
      // $count = mysql_fetch_array ( $res, MYSQL_ASSOC );
      // $count = $count ['runde'];
      // mysql_data_seek ( $res, 0 );

      // Paarungen durchgehen
      $lastr = false;
      while ( $paarung = mysql_fetch_array ( $res, MYSQL_ASSOC ) )
      {
        // Nächste Runde?
        if ( $paarung ['runde'] != $lastr )
        {
          // Datum berechnen
          $paarung ['termin'] = SED_GetTermin ( $paarung ['runde'], $_GET ['staffel'] );

          // Ausgabe der Spieltagüberschrift
          if ( $lastr != 0 )
            echo "<tr><td colspan='5'>&nbsp;</td></tr>";
          echo "<tr><td colspan='5'><a href='?staffel=$_GET[staffel]&amp;r=$paarung[runde]'><b>$paarung[runde]. Spieltag - $paarung[termin]</b></a></td></tr>";
          $lastr = $paarung ['runde'];
        }

        // Paarungsausgabe
        echo "<tr><td>";
        echo SED_TeamLink ( $paarung ['mannschaft1'] );
        echo "</td><td>-</td><td>";
        echo SED_TeamLink ( $paarung ['mannschaft2'] );
        if ( $paarung ['isset'] ){
			echo "</td><td class='c'>&nbsp;" . SED_Ergebnis ( $paarung ['erg1'] ) . ":" . SED_Ergebnis ( $paarung ['erg2'] ) . "</td>";
		} else if (is_numeric($paarung ['ausrichter'])) {
			echo "</td><td class='l'>&nbsp;(Ausrichter: ".SED_TeamLink($paarung['ausrichter']).')</td>';
        } else {
			echo "</td><td></td>";
		}
        echo "<td>{$paarung['terminAbw']}</td>";
        echo "</tr>";
      }
  }
  echo "</table>";

?>
