<?
/* SL-Bereich: Desktop
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


  // Konfiguration
  global $desktopRowLength;
  $desktopRowLength = 5;

  // Gruppe ausgeben
  function AdminEchoGroup ( $id )
  {
    global $globals;
    global $admin;
    global $desktopRowLength;
    global $prefs;

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
        echo "<td class='sed_admin_desk_icon'><a href='?admin=".$admin ['pagelib'][$j][0]."-$admin[userid]-$admin[session]'>
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

  // Ausgabe
  echo "<form action='".SED_GenerateFormAction()."' method='get'><div>";

  // Meine Staffel
  if ( $admin ['usertype'] == "s" )
    AdminEchoGroup ( 4 );

  // Spielauflistung (React component)
  echo "<fieldset class='sed_admin_desk'><legend>Paarungseingabe</legend>";
  echo "<div data-nsv-component='PairingList' data-nsv-division='$admin[staffel]'></div>";
  echo "</fieldset><br><br>";

  // Staffelverwaltung
  if ( $admin ['usertype'] == "t" )
  {
    echo "<fieldset class='sed_admin_desk'><legend>Staffelverwaltung</legend><table cellspacing='0' cellpadding='3'>";

    // Staffeln auflisten
    foreach ( $globals ['staffeln'] as $id => $name )
    {
      echo "<tr><td><a href='?admin=turnstbe-$admin[userid]-$admin[session]&staffel=$id' style='text-decoration:none'>$name&nbsp;&nbsp;</a></td><td style='min-width:330px'>
              <a style='text-decoration:none;' href='?admin=turnstbe-$admin[userid]-$admin[session]&staffel=$id'><img src='$globals[systemicons]desk_bearbeiten.png' alt='Bearbeiten' class='sed_admin_icon' />Bearbeiten</a>
              &nbsp;<a href='?admin=stafbeme-$admin[userid]-$admin[session]&staffel=$id'><img src='$globals[systemicons]desk_bemerkungen.png' alt='Bemerkungen' class='sed_admin_icon' /></a>
              &nbsp;<a href='?admin=stafrund-$admin[userid]-$admin[session]&staffel=$id'><img src='$globals[systemicons]desk_rundmail.png' alt='Rundmail' class='sed_admin_icon' /></a>
              &nbsp;<a href='?admin=turnstsp-$admin[userid]-$admin[session]&staffel=$id'><img src='$globals[systemicons]desk_spielplan.png' alt='Spielplan' class='sed_admin_icon' /></a>
              &nbsp;<a href='?admin=stafzuga-$admin[userid]-$admin[session]&staffel=$id'><img src='$globals[systemicons]desk_zugangsdaten.png' alt='Zugangsdaten' class='sed_admin_icon' /></a>
              &nbsp;<a href='?admin=stafeins-$admin[userid]-$admin[session]&staffel=$id'><img src='$globals[systemicons]desk_einstellungen.png' alt='Einstellungen' class='sed_admin_icon' /></a>
            </td></tr>";
    }

    // Neue Staffel
    echo "<tr><td colspan='2'>";
    echo "<a data-nsv-dialog='CreateDivision' data-nsv-on-save='reload' style='cursor: pointer; text-decoration:none'><img src='$globals[systemicons]desk_neu.png' alt='Neue Staffel' class='sed_admin_icon' />Neue Staffel</a>";
    echo "<a data-nsv-dialog='SortDivisions' data-nsv-on-save='reload' style='cursor: pointer; text-decoration:none'><img src='$globals[systemicons]timestamp.png' alt='Staffeln umsortieren' class='sed_admin_icon' />Staffeln umsortieren</a>";
    echo "</td></tr>";

    // Fieldset
    echo "</table></fieldset><br /><br />";
  }

  // Mein Turnier
  if ( $admin ['usertype'] == "t" )
    AdminEchoGroup ( 1 );

  // Mannschaftsverwaltung
  if ( $admin ['usertype'] == "s" )
  {
    // Mannschaften abfragen
    $res = mysql_query ( "SELECT id FROM mannschaften WHERE staffel=$admin[staffel] ORDER BY name, mnr, id", $globals ['db'] );
    if ( $res && mysql_num_rows ( $res ) )
    {
        // Ausgabe
        echo "<fieldset class='sed_admin_desk'><legend>Mannschaftsverwaltung</legend><table cellspacing='0' cellpadding='3'>";
          while ( $team = mysql_fetch_array ( $res, MYSQL_ASSOC ) )
          {
            echo "<tr><td>".$globals['teams'][$team['id']]."&nbsp;&nbsp;</td><td>
                    <a style='text-decoration: none' href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$team[id]&edit=0'><img src='$globals[systemicons]desk_nachmeldung.png' alt='Nachmeldungen' class='sed_admin_icon' />Nachmeldung</a>
                    <a style='text-decoration: none' href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$team[id]'><img src='$globals[systemicons]desk_spieler.png' alt='Spieler' class='sed_admin_icon' />Spieler</a>
                    <a style='text-decoration: none' href='m/$team[id]/'><img src='$globals[systemicons]desk_bearbeiten.png' alt='Mannschaftsf&uuml;hrer' class='sed_admin_icon' />Bearbeiten</a>
                  </td></tr>";
          }
        echo "</table></fieldset><br /><br />";
    }
  }

  // Auflistung der Mannschaften ohne Staffel
  if ( $admin ['usertype'] == "t" )
  {
    // Mannschaften abfragen
    $res = mysql_query ( "SELECT id FROM mannschaften WHERE staffel=0 AND turnier=$globals[tid] ORDER BY name, mnr, id", $globals ['db'] );
    if ( $res && mysql_num_rows ( $res ) )
    {
        // Ausgabe
        echo "<fieldset class='sed_admin_desk'><legend>Mannschaften ohne Staffel</legend>Im folgenden sind alle Mannschaften aufgelistet, denen noch keine Staffel zugeordnet wurde:<br /><br /><table cellspacing='0' cellpadding='3'>";

        // Mannschaften auflisten
          while ( $team = mysql_fetch_array ( $res, MYSQL_ASSOC ) )
          {
            echo "<tr><td>".$globals['teams'][$team['id']]."&nbsp;&nbsp;</td><td>
                    <a style='text-decoration: none' href='?admin=stafspie-$admin[userid]-$admin[session]&mid=$team[id]'><img src='$globals[systemicons]desk_spieler.png' alt='Spieler' class='sed_admin_icon' />Spieler</a>
                    <a style='text-decoration: none' href='m/$team[id]/'><img src='$globals[systemicons]desk_bearbeiten.png' alt='Bearbeiten' class='sed_admin_icon' />Bearbeiten</a>
                    <a style='text-decoration: none' href='?admin=turnmalo-$admin[userid]-$admin[session]&mid=$team[id]'><img src='$globals[systemicons]desk_loeschen.png' alt='' class='sed_admin_icon' />L&ouml;schen</a>
                  </td></tr>";
          }

        // Fieldset
        echo "</table></fieldset><br /><br />";
    }
  }

  // Zusatzfunktionen
  if ( $admin ['usertype'] == "t" )
    AdminEchoGroup ( 2 );

  // Ausgabe beenden
  echo "</div></form>";
