<?
/* SL-Bereich: Turniermenü
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

  // Wenn Daten abgesendet wurden
  if ( isset ( $_POST ['menue_save'] ) )
  {
    // Alte Links löschen
    mysql_query ( "DELETE FROM turniermenue WHERE turnier=$globals[tid] LIMIT 100", $globals ['db'] );

    // Neue Links einfügen
    for ( $i = 1; isset ( $_POST ["linkTitel$i"] ); ++$i )
    {
      if ( $_POST ["linkTitel$i"] )
      {
        $neuesfenster = (int) ( isset ( $_POST ["linkNeu$i"] ) && $_POST ["linkNeu$i"] );
        mysql_query ( "INSERT INTO turniermenue SET turnier=$globals[tid], sortid=$i, titel='".$_POST["linkTitel$i"]."', url='".$_POST["linkUrl$i"]."', neuesfenster=$neuesfenster", $globals ['db'] );
      }
    }

    echo "<b>Das neue Men&uuml; wurde gespeichert!</b>";
    echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]' />";
  }



  // Fügt eine Zeile hinzu
  function __AddForm ( $titel, $url, $neuesfenster )
  {
    global $globals;
    static $id;
    $id++;

    $checked = $neuesfenster ? "checked='checked'" : "";
    echo "<tr>
            <td><input type='text' name='linkTitel$id' value='$titel' size='20' maxlength='30' /></td>
            <td><input type='text' name='linkUrl$id' value='$url' size='20' maxlength='100' /></td>
            <td><input type='checkbox' name='linkNeu$id' value='1' title='In neuem Fenster &ouml;ffnen?' $checked />
                <a href='javascript:moveUp($id)'><img src='$globals[systemicons]up.gif' class='sed_admin_icon' alt='Hoch' /></a>
                <a href='javascript:moveDown($id)'><img src='$globals[systemicons]down.gif' class='sed_admin_icon' alt='Runter' /></a>
                <a href='javascript:remove($id)'><img src='$globals[systemicons]desk_loeschen.png' class='sed_admin_icon' alt='Weg' /></a>
            </td>
         </tr>";
  }
?>

  Das Turniermen&uuml; wird in der Regel in der Seitenleiste angezeigt. &Uuml;ber das Men&uuml; k&ouml;nnen Sie wichtige Seiten oder Dateien verlinken, z.B. das
  Saisonheft als PDF. Sie k&ouml;nnen auch entscheiden, ob der Link in einem neuen Fenster ge&ouml;ffnet werden soll.<br /><br />

    <script type='text/javascript'><!--

        function moveUp ( id ){
            swap ( id-1, id );
        }
        function moveDown ( id ){
            swap ( id, id+1 );
        }
        function swap ( a, b ){
            swapExt ( a, b, "Titel", "value" );
            swapExt ( a, b, "Url", "value" );
            swapExt ( a, b, "Neu", "checked" );
        }
        function swapExt ( a, b, name, attribut ){
            // Objekt-Referenz holen, überprüfen ob tauschen möglich ist
            a = document.getElementsByName ( "link"+name+a )[0]; 
            b = document.getElementsByName ( "link"+name+b )[0];
            if ( !a || !b ) return;
            
            // Tauschen
            if ( attribut == "checked" ){
                tmp = a.checked; a.checked=b.checked; b.checked=tmp;
            } else {
                tmp = a.value; a.value=b.value; b.value=tmp;
            }
        }
        function remove ( id ){
            document.getElementsByName ("linkTitel"+id)[0].value = "";
            document.getElementsByName ("linkUrl"+id)[0].value = "";
            document.getElementsByName ("linkNeu"+id)[0].checked = false;
        }
        function add ( name, url ){
            for ( i = 1; titel=document.getElementsByName("linkTitel"+i)[0]; ++i ){
                if ( titel.value == "" ){
                    titel.value = name;
                    document.getElementsByName("linkUrl"+i)[0].value = url;
                    break;
                }
            }
        }


    --></script>

  <form action="<? echo SED_GenerateFormAction(); ?>" method="post" style="text-align: left;">
    <table cellspacing="0" cellpadding="2">
      <tr><th>Beschriftung</th><th>URL</th><th></th></tr>

        <?
          // Bisherige Links abfragen
          $rsrc = mysql_query ( "SELECT * FROM turniermenue WHERE turnier=$globals[tid] ORDER BY sortid", $globals ['db'] );

          // Bisherige Links ausgeben
          if ( mysql_num_rows ( $rsrc ) )
            while ( $tmp = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) )
              __AddForm ( "$tmp[titel]", "$tmp[url]", "$tmp[neuesfenster]" );

          // Weitere Felder ausgeben
          for ( $i = 0; $i < 5; ++$i )
            __AddForm ( "", "", 0 );
        ?>

      <tr><td colspan='3'><input type="submit" class="sed_submit" name="menue_save" value="Speichern" /> <input type='button' class='sed_submit' value='Abbrechen' onclick="<? echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" /></td></tr>
    </table>
  </form><br />

<?
    $rsrc = mysql_query ( "SELECT name, directory, id FROM turniere t WHERE t.organisation='$prefs[organisation]' AND (t.startjahr='$prefs[startjahr]' OR t.startjahr='$prefs[startjahr]'-1) AND t.id<>$globals[tid] ORDER BY t.startjahr DESC", $globals ['db'] );
    if ( $rsrc && mysql_num_rows ( $rsrc ) ){
        echo "<span class='sed_hl2'>Vorschl&auml;ge:</span><br /><br />";
        while ( $turnier = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ){
            // Link zu dem Turnier
			echo "<a href='javascript:add(\"$turnier[name]\",\"$globals[httppath]$turnier[directory]/?esw=1\")'>$turnier[name]</a><br />";
			
			// Turniermenüeinträge der letzten Saison
			$lastMenu = mysql_query ( "SELECT * FROM turniermenue WHERE turnier=$turnier[id] ORDER BY sortid", $globals ['db'] );
			if ( mysql_num_rows ( $lastMenu ) ){
				while ( $tmp = mysql_fetch_array ( $lastMenu, MYSQL_ASSOC ) ){
					echo "<a href='javascript:add(\"$tmp[titel]\",\"$tmp[url]\")'>$tmp[titel]</a><br />";
				}
			}
        }
    }
?>
