<?php
/* SL-Bereich: Staffel bearbeiten
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

  // Mannschaftsverwaltung
  echo "<form action='index.php' method='get'><div>";
  {
    // Mannschaft löschen
    if ( isset ( $_GET ['delmann'] ) )
    {
      SED_TryQuery('UPDATE mannschaften SET staffel=0 WHERE id=? AND turnier=? LIMIT 1', [$_GET['delmann'], $globals['tid']]);
      SED_Cache::clearTables ( $admin ["staffel"] );
    }

    // Mannschaft hinzufügen
    if ( isset ( $_GET ['addmann'] ) )
    {
      SED_TryQuery('UPDATE mannschaften SET staffel=? WHERE id=? AND turnier=? LIMIT 1', [$admin['staffel'], $_GET['addmann'], $globals['tid']]);
      SED_Cache::clearTables ( $admin ["staffel"] );
    }

    // Mannschaftsverwaltung
    echo "<fieldset class='sed_admin_desk'><legend>Mannschaftsverwaltung</legend><table cellspacing='0' cellpadding='3'>";
    $teams = SED_Query('SELECT id FROM mannschaften WHERE staffel=? ORDER BY name, mnr, id', [$admin['staffel']])->fetchAllAssociative();

    // Mannschaften auflisten
    foreach ($teams as $team)
    {
        echo "<tr><td>".$globals ['teams'][$team ['id']]."&nbsp;&nbsp;</td><td>
                <a style='text-decoration: none' href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$team[id]&edit=0'><img src='$globals[systemicons]desk_nachmeldung.png' alt='Nachmeldungen' class='sed_admin_icon' />Nachmeldung</a>
                <a style='text-decoration: none' href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$team[id]'><img src='$globals[systemicons]desk_spieler.png' alt='Spieler' class='sed_admin_icon' />Spieler</a>
                <a style='text-decoration: none' href='m/$team[id]/'><img src='$globals[systemicons]desk_bearbeiten.png' alt='Bearbeiten' class='sed_admin_icon' />Bearbeiten</a>
                <a style='text-decoration: none' href='?admin=turnstbe-$admin[userid]-$admin[session]&staffel=$admin[staffel]&delmann=$team[id]'><img src='$globals[systemicons]desk_entfernen.png' alt='Aus Staffel entfernen' class='sed_admin_icon' /></a>
              </td></tr>";
    }

    // Neue Mannschaft
    $availableTeams = SED_Query('SELECT id FROM mannschaften WHERE turnier=? AND staffel=0 ORDER BY name, mnr, id', [$globals['tid']])->fetchAllAssociative();
    echo "<tr><td colspan='2'><input type='hidden' name='admin' value='turnstbe-$admin[userid]-$admin[session]' /><input type='hidden' name='staffel' value='$admin[staffel]' /><select name='addmann'>";
    foreach ($availableTeams as $team)
      echo "<option value='".$team['id']."'>" . $globals['teams'][$team['id']] . "</option>";
    echo "</select> <input type='submit' class='sed_submit' value='Hinzuf&uuml;gen' /></td></tr>";

    // Form End
    echo "</table></fieldset><br /><br />";
  }

  // LINKS
  {
    // Konfiguration
    global $desktopRowLength;
    $desktopRowLength = 5;

    // Gruppe ausgeben
    function AdminEchoGroup ( $id )
    {
      global $globals;
      global $admin;
      global $desktopRowLength;

      // Fieldset ausgeben und Tabelle starten
      echo "<fieldset class='sed_admin_desk'><legend>" .$admin ['groups'][$id][1]. "</legend><table class='sed_admin_desk' cellspacing='0' cellpadding='0'>";

      // Icons ausgeben
      $i = 0;
      for ( $j = 0; $j < count ( $admin ['pagelib'] ); ++$j )
      {
        if ( $admin ['pagelib'][$j][1] == $id )
        {
          // Neue Zeile anfangen
          if ( $i % $desktopRowLength == 0 )
            echo "<tr>";

          // Icon ausgeben
          echo "<td class='sed_admin_desk_icon'><a href='?admin=".$admin ['pagelib'][$j][0]."-$admin[userid]-$admin[session]&staffel=$admin[staffel]'>
                <img src='$globals[systemicons]".$admin ['pagelib'][$j][0].".png' alt='' title='". $admin ['pagelib'][$j][6] . "' style='border: none;' /><br />
                " . $admin ['pagelib'][$j][4] . "
                </a></td>";

          // Zeile beenden
          if ( $i % $desktopRowLength == $desktopRowLength - 1 )
            echo "</tr>";
          ++$i;
        }
      }

      // Fieldset
      echo "</table></fieldset><br /><br />";
    }

    AdminEchoGroup ( 6 );
  }

  echo "</div></form>";

?>
