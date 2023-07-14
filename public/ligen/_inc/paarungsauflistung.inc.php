<?
/* SL-Bereich: Liste von Paarungen
 *
 * Liefert eine Liste von Paarungen einer bestimmten Staffel und
 * Runde. Kann auch über AJAX benutzt werden.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

  function SED_Paarungsauflistung ()
  {
    global $admin, $globals, $prefs;

    require_once ( "login.inc.php" );

    // Bei Mannschaft alle Spieltage ausgeben
    $rsrc = $staffelwhere = $rundenwhere = false;

      // Staffel WHERE-Klausel
      if ( $admin ['usertype'] == "s" )
        $staffelwhere = "p.staffel=$admin[staffel]";
      elseif ( isset ( $_GET ['staffel'] ) && is_numeric ( $_GET ['staffel'] ) )
        $staffelwhere = "p.staffel=$_GET[staffel]";
      else // alle Staffeln
        $staffelwhere = "1";

      // Runden WHERE-Klausel
      if ( isset ( $_GET ['runde'] ) == false || $_GET ['runde'] == "akt" )
      {
        require_once ( "runde.inc.php" );
        $rundenwhere = "p.runde=$_GET[r]";
      }
      elseif ( is_numeric ( $_GET ['runde'] ) ) // nur eine bestimmte
        $rundenwhere = "p.runde=$_GET[runde]";
      else // alle ausgeben
        $rundenwhere = "1";

      // Abfrage ausführen
      $rsrc = mysql_query ( "SELECT p.id as pid, p.runde as runde, p.mannschaft1 as m1, p.mannschaft2 as m2, p.erg1 IS NOT NULL as gesetzt, 0 as festgelegt FROM paarungen p INNER JOIN staffeln s ON s.id=p.staffel WHERE s.turnier=$globals[tid] AND $staffelwhere AND $rundenwhere ORDER BY runde", $globals ['db'] );


    // Spielauflistung - Ausgabe
    if ( $rsrc )
    {
      echo "<br /><table cellspacing='0' cellpadding='3' class='sed_tabelle'><tr><th>&nbsp;</th><th>Paarung</th><th>Status</th></tr>";
      while ( $row = mysql_fetch_array ( $rsrc, MYSQL_BOTH ) )
      {
        // Paarungstitel ausgeben
        echo "<tr>";
        echo "<td>$row[runde].</td>";
        echo "<td style='text-align:left'><a href='?admin=alleeing-$admin[userid]-$admin[session]&pid=$row[pid]#admintop' style='text-decoration:none'>".$globals ['teams'][$row ['m1']]." - ".$globals ['teams'][$row ['m2']]."</a></td>";

        // Link zu den Einstellungen
        $settings = " <a href='?admin=alleeing-$admin[userid]-$admin[session]&pid=$row[pid]#zusatz'><img src='$globals[systemicons]desk_einstellungen.png' alt='Einstellungen' class='sed_admin_icon' />Optionen&nbsp;</a>";

        // Wenn die Paarung setzbar ist
        if ( $row ['gesetzt'] )
          echo "<td style='text-align:left; min-width:220px'><a href='?admin=alleeing-$admin[userid]-$admin[session]&pid=$row[pid]#admintop'><img src='$globals[systemicons]desk_eingeben.png' alt='Eingeben' class='sed_admin_icon' />&Auml;ndern</a>$settings</td>";
        else
          echo "<td style='text-align:left; min-width:220px'><a href='?admin=alleeing-$admin[userid]-$admin[session]&pid=$row[pid]#admintop'><img src='$globals[systemicons]desk_eingeben.png' alt='Eingeben' class='sed_admin_icon' />Eingeben</a>$settings</td>";
        echo "</tr>";
      }
      echo "</table>";
    }
  }
?>
