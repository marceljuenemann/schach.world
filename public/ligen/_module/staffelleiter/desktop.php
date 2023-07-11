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

  // Spielauflistung
  {
    // Vorbereitung
    echo "<a name='peingabe'></a><fieldset class='sed_admin_desk'><legend>Paarungseingabe</legend>";

    // Auswahl-Formular
    if ( $admin ['usertype'] != "m" )
    {
        echo "<form action='' method='get'><div><input type='hidden' name='admin' value='desktop-$admin[userid]-$admin[session]' id='pl_hidden' />Selektieren: ";

        // Staffelauswahl
        if ( $admin ['usertype'] == "t" )
        {
            $options = "<option value='alle'>Alle Staffeln</option>";
            foreach ( $globals ['staffeln'] as $k => $v )
              $options .= "<option value='$k'>$v</option>";
            $options = SED_SelectOption ( $options, isset ( $_GET ['staffel'] ) ? $_GET ['staffel'] : "nichts" );
            echo "<select name='staffel' id='pl_staffel' onchange='js_SED_GetPaarungsauflistung();' style='font-family: Tahoma,Verdana; font-size: 10pt;'>$options</select>&nbsp;&nbsp;";
        }
        else // Bei Staffelleitern
            echo "<input type='hidden' name='staffel' id='pl_staffel' value='".$admin['staffel']."' />";

        // Rundenauswahl
        $options = "<option value='akt'>Aktuelle Runde</option><option value='alle'>Alle Runden</option>";
        for ( $i = 1; $i <= $prefs ['runden']; ++$i )
          $options .= "<option value='$i'>Nur $i. Spieltag</option>";
        $options = SED_SelectOption ( $options, isset ( $_GET ['runde'] ) ? $_GET ['runde'] : "nichts" );
        echo "<select name='runde' onchange='js_SED_GetPaarungsauflistung();' id='pl_runde' style='font-family: Tahoma,Verdana; font-size: 10pt;'>$options</select>&nbsp;&nbsp;";

        echo "<input type='submit' class='submit' value='Anzeigen' /></div></form>";
    }

    // Paarungen ausgeben
    echo "<div id='pl_content'>";
    require_once ( "paarungsauflistung.inc.php" );
    SED_Paarungsauflistung ();
    echo "</div>";

    // Ende
    echo "</fieldset><br /><br />";

    // Javascript
    ?>
    <script type='text/javascript'><!--

      function js_SED_GetPaarungsauflistung ()
      {
        {
          // URL zusammensetzen
          var admin = document.getElementById ( "pl_hidden" ).value;
          var staffel = document.getElementById ( "pl_staffel" ).value;
          var runde = document.getElementById ( "pl_runde" ).value;
          var url = '<? echo "$globals[httppath]index.php?tid=$globals[tid]&type=GetPaarungsauflistung"; ?>' + "&admin=" + admin + "&staffel=" + staffel + "&runde=" + runde;

          // Ajax initialisieren
          var req = null;
          try { req = new XMLHttpRequest(); }
          catch (e) {
            try { req = new ActiveXObject('Msxml2.XMLHTTP'); }
            catch (e) {
              try { req = new ActiveXObject('Microsoft.XMLHTTP'); }
              catch ( failed ) { req = null; }
            }
          }


          // Anfrage stellen
          if ( req != null )
          {
            // Anfrage abschicken
            req.open ( 'GET', url, true );

            // Bei Antwort folgendes ausführen:
            req.onreadystatechange = function ()
            {
              if ( req.readyState == 4 && req.status == 200 )
              {
                document.getElementById ( "pl_content" ).innerHTML = req.responseText;
                return true;
              }
              return false;
            };

            // Endgültig senden
            req.setRequestHeader ( 'Content-Type', 'application/x-www-form-urlencoded' );
            req.send ( null );
          }
        }
      }

    --></script>
    <?

  }

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
    echo "<tr><td colspan='2'><a href='?admin=turnstne-$admin[userid]-$admin[session]' style='text-decoration:none'><img src='$globals[systemicons]desk_neu.png' alt='Neue Staffel' class='sed_admin_icon' />Neue Staffel</a></td></tr>";

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
                    <a style='text-decoration: none' href='?admin=manndata-$admin[userid]-$admin[session]&mid=$team[id]'><img src='$globals[systemicons]desk_bearbeiten.png' alt='Mannschaftsf&uuml;hrer' class='sed_admin_icon' />Bearbeiten</a>
                    <a style='text-decoration: none' href='?admin=manndata-$admin[userid]-$admin[session]&mid=$team[id]'><img src='$globals[systemicons]desk_spiellokal.png' alt='Spiellokal' class='sed_admin_icon' /></a>
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
                    <a style='text-decoration: none' href='?admin=manndata-$admin[userid]-$admin[session]&mid=$team[id]'><img src='$globals[systemicons]desk_bearbeiten.png' alt='Bearbeiten' class='sed_admin_icon' />Bearbeiten</a>
                    <a style='text-decoration: none' href='?admin=manndata-$admin[userid]-$admin[session]&mid=$team[id]'><img src='$globals[systemicons]desk_spiellokal.png' alt='Spiellokal' class='sed_admin_icon' /></a>
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

?>
