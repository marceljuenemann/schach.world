<?
/* Turnier-Startseite
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

    // Überschriften
    echo "<span class='sed_hl1'>$prefs[name]</span><br /><br />";
    echo "<table cellspacing='0' cellpadding='0' class='sed_home'><tr><td class='l' style='vertical-align: top'>";

    // Aktuelle Ergebnisse
    if ( count ( $globals ['staffeln'] ) ){
        // Datum false bedeutet, dass jede Staffel ihre aktuelle Runde berechnet
        $datum = false;
        $showNext = true;
        $showPrev = true;

        // Ein Termin danach gefordert?
        if ( isset ( $_GET["go_after"] ) ){
            $rsrc = mysql_query ( "SELECT DISTINCT datum FROM viewStaffeltermine WHERE turnier=$globals[tid] AND datum>'$_GET[go_after]' ORDER BY datum ASC LIMIT 2", $globals['db'] );
            if ( $rsrc && mysql_num_rows ( $rsrc ) ){
                $datum = reset ( mysql_fetch_array ( $rsrc, MYSQL_NUM ) );
                if ( mysql_num_rows ( $rsrc ) == 1 )
                    $showNext = false;
            } else {
                $showNext = false;
            }
        }

        // Ein Termin davor gefordert?
        if ( isset ( $_GET["go_before"] ) ){
            $rsrc = mysql_query ( "SELECT DISTINCT datum FROM viewStaffeltermine WHERE turnier=$globals[tid] AND datum<'$_GET[go_before]' ORDER BY datum DESC LIMIT 2", $globals['db'] );
            if ( $rsrc && mysql_num_rows ( $rsrc ) ){
                $datum = reset ( mysql_fetch_array ( $rsrc, MYSQL_NUM ) );
                if ( mysql_num_rows ( $rsrc ) == 1 )
                    $showPrev = false;
            } else {
                $showPrev = false;
            }
        }

        // Überschrift
        if ( $datum )
            echo "<span class='sed_hl2'>Spiele am ".substr($datum,8,2).".".substr($datum,5,2).".".substr($datum,0,4)."</span>";
        else
            echo "<span class='sed_hl2'>Aktueller Spieltag</span>";
        $go_date = $datum ? $datum : strftime ( "%Y-%m-%d" );
        if ( $showPrev )
            echo "&nbsp;&nbsp;<a href='?go_before=$go_date'><img style='border: none' alt='Vorheriger Spieltag' src='$globals[systemicons]pre.png' /></a>";
        if ( $showNext )
            echo "&nbsp;&nbsp;<a href='?go_after=$go_date'><img alt='Nächster Spieltag' src='$globals[systemicons]next.png' style='border: none' /></a>";
        echo "<br /><table class='sed_normal' cellspacing='0' cellpadding='3'>";

        // Alle Staffeln durchgehen
        foreach ( $globals ['staffeln'] as $id=>$staffel ){
            // Anzuzeigende Runden berechnen
            $runden = array();
            $rundentermin = $datum;
            if ( $rundentermin ){
                // Runde nach Datum finden
                $rsrc = mysql_query ( "SELECT runde FROM viewStaffeltermine WHERE id=$id AND datum='$datum' ORDER BY runde", $globals ['db'] );
                if ( $rsrc )
                    while ($runde = mysql_fetch_array ( $rsrc, MYSQL_NUM ))
                        $runden[] = reset($runde);
            } else {
                $rsrc = mysql_query ( "SELECT runde, datum FROM viewStaffeltermine st WHERE id=$id AND EXISTS (SELECT 1 FROM paarungen WHERE staffel=st.id AND runde=st.runde) ORDER BY ABS(UNIX_TIMESTAMP(datum)-UNIX_TIMESTAMP(CURDATE())), runde LIMIT 3", $globals ['db'] );
                while ( $rsrc && $tmp = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ) {
                    if ($rundentermin && $tmp["datum"] != $rundentermin) break;
                    $runden[] = $tmp["runde"];
                    $rundentermin = $tmp["datum"];
                }
            }

            // Anzeigen
            foreach ( $runden as $runde ){
                // Ergebnisse abfragen
                $rsrc = mysql_query ( "SELECT p.mannschaft1, p.mannschaft2, p.erg1, p.erg2, (p.erg1 IS NOT NULL) as isset, p.termin FROM paarungen as p WHERE p.staffel=$id AND p.runde='$runde' ORDER BY p.id", $globals ['db'] );

                // Staffelüberschrift ausgeben
                if ( $rsrc && mysql_num_rows ( $rsrc ) ){
                    echo "<tr><th colspan='2' style='text-align: left'><br />";
                    echo "<a href='?staffel=$id&amp;r=$runde'>" . $globals ['staffeln'][$id];
                    echo "<br />".substr($rundentermin,8,2).".".substr($rundentermin,5,2).".".substr($rundentermin,0,4)." - $runde. Spieltag</a>";
                    echo "</th><td colspan='3' style='vertical-align: bottom'><br />";
                    echo "<a href='?staffel=$id&amp;r=$runde'><img style='border: none' alt='HTML-Version' src='$globals[systemicons]spieltag.gif' /></a>";
                    echo "&nbsp;<a href='?staffel=$id&amp;r=$runde&ausgabe=pdf'><img style='border: none' alt='Druckversion' src='$globals[systemicons]printer.gif' /></a>";
                    echo "&nbsp;<a href='?staffel=$id&r=statistik'><img style='border: none' alt='Statistiken' src='$globals[systemicons]statistiken.gif' /></a>";
                    echo "</td></tr>";

                    // Paarungen ausgeben
                    while ( $temp = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ){
                        echo "<tr><td>" . SED_TeamLink ( $temp ['mannschaft1'] ) . "</td><td> - " . SED_TeamLink ( $temp ['mannschaft2'] ) . "</td>";
                        if ( $temp ["isset"] )
                            echo "<td style='text-align:right'>" . SED_Ergebnis ( $temp ['erg1'] ) . "</td><td>:</td><td>" . SED_Ergebnis ( $temp ['erg2'] ) . "</td>";
                        else {
                            echo "<td colspan='3' style='text-align:center'>";
                            if ( strlen ( $temp ["termin"] ) ){
                                if ( "2020-12-24" == $temp ["termin"] )
                                    echo "(verl.)";
                                else
                                    echo "(".substr($temp["termin"],8,2).".".substr($temp["termin"],5,2).".)";
                            }
                            echo "</td>";
                        }
                        echo "</tr>";
                    }
                }
            }
        }

        echo "</table><br /><br />";
    }


  echo "</td><td class='r'>";
  if (!SED_IsNsv2020()) {
  ?>
    <span class="sed_hl2">Staffelleiter&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><br /><br />
      <form style='margin: 0px; margin-top: 5px; text-align: center;' action="?admin=login" method="post"><div>
        <select style='font-family: Tahoma, Verdana; font-size: 10pt; margin-bottom: 6px; width: 85%;' name="benutzer">
          <?
            $benutzer = isset ( $_GET ['staffel'] ) ? $_GET ['staffel'] : ""; // nur über tinyurl möglich
            echo "<option value='t-$globals[tid]'>--- Benutzer ---</option>";
            foreach ( $globals ['staffeln'] as $id=>$name ){
                $selected = $benutzer == $id ? "selected='selected'" : "";
                echo "<option value='s-$id' $selected>$name</option>";
            }
            echo "<option value='t-$globals[tid]'>Turnierleiter</option>";
          ?>
        </select><br />
        <input type="password" name="passwort" style="font-family: Tahoma, Verdana; font-size: 10pt; margin-bottom: 5px; padding-left: 2px; padding-right: 2px; width: 80%;" /><br />
        <input type="submit" value="Einloggen" class="sed_submit" />
      </div></form>

    <? if ( !$prefs ['sysKeinNewsletter'] ) { ?>
      <br /><br />
      <span class="sed_hl2">Newsletter</span><br /><br />
        Tragen Sie sich in dieses Formular ein, um alle Rundschreiben für eine bestimmte Staffel per Email zu erhalten.<br /><br />
        <form style='margin: 0px; margin-top: 5px; text-align: center;' action="?m=newsletter" method="post"><div>
          <select style='font-family: Tahoma, Verdana; font-size: 10pt; margin-bottom: 6px; width: 85%;' name="staffel">
            <?
              echo "<option value='1'>--- Staffel ---</option>";
              foreach ( $globals ['staffeln'] as $id=>$name )
                echo "<option value='$id'>$name</option>";
            ?>
          </select><br />
          <input type="text" name="email" value="Email" onfocus="this.value = '';" style="font-family: Tahoma, Verdana; font-size: 10pt; margin-bottom: 5px; padding-left: 2px; padding-right: 2px; width: 80%;" /><br />
          <input type="submit" name="newsletter_register" value="Registrieren" class="sed_submit" />
        </div></form>
    <? } ?>
  <?
  }

  echo "</td></tr></table>";
?>
