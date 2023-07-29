<?
/* Mannschaftsaufstellung und ähnliches
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage mannschaft
 */
    require_once ( "turnier.inc.php" );
    require_once ( "mannschaft.class.php" );
    require_once ( "gui.inc.php" );

    // Weiterleitung, wenn die Mannschaft nicht zum Turnier gehört
    if ( !isset ( $globals ["teams"][$_GET ["mannschaft"]] ) )
    {
        // Bei ungültiger Mannschafts-ID zum richtigen Turnier weiterleiten
        $tname = reset ( mysql_fetch_array ( mysql_query ( "SELECT t.directory FROM mannschaften m INNER JOIN turniere t ON t.id=m.turnier WHERE m.id='$_GET[mannschaft]'", $globals ['db'] ), MYSQL_ASSOC ) );
        if ( !$tname ) SED_Error ( "Ung&uuml;ltige Mannschafts-ID!?", true );
        echo "<b>Sie werden weitergeleitet...</b>";
        echo "<meta http-equiv='refresh' content='0;URL=$globals[httppath]$tname/?mannschaft=$_GET[mannschaft]' />";
        exit;
    }

    // Objekt erstellen
    $team = new SED_Mannschaft ( $_GET ["mannschaft"] );

    // Links und Mailadressen vorbereiten
    $staffel = count ( $globals ['staffeln'] ) > 1 ? "<b>Staffel: </b><a href='?staffel=".$team->get("staffel")."&r='>".$globals["staffeln"][$team->get("staffel")]."</a><br /><br />" : "";
    $email = SED_Email ( $team->get( 'mf_email' ), $team->get( 'mf_email' ), 20 );
    $stadtplan = ( "http://maps.google.com/maps?hl=de&amp;q=".$team->get("so_strasse").", ".$team->get("so_plz")." ".$team->get("so_stadt").", Germany" );
    $routenplaner = ( "http://maps.google.com/maps?hl=de&amp;saddr=&amp;daddr=".$team->get("so_strasse").", ".$team->get("so_plz")." ".$team->get("so_stadt").", Germany" );

    // Ausgabe der allgemeinen Informationen
    echo "<span class='sed_hl1'>".$globals ['teams'][$_GET ['mannschaft']]."</span><br /><br />";
    echo "<div class='row'><div class='col-12 col-md-5'>";
        
    echo "  <span class='sed_hl2'>Informationen</span><br /><br />
            $staffel
            <b>Spielort:</b><br />
            ".$team->get("so_name").
            "<br />".
            ($team->get("so_hinweis")?"Hinweis: ".$team->get("so_hinweis")."<br />":"").
            $team->get("so_strasse")."<br />".$team->get("so_plz")." ".$team->get("so_stadt");
    if (trim($team->get("so_telefon"))) {
      echo "<br />Tel.: ".$team->get("so_telefon");
    }
    echo "  <span class='sed_only_screen d-print-none'><br />
      " . ($team->getZusatzfeldBoolean(SED_utf8_decode('Verfügt das Spiellokal über barrierefreien Zugang?')) ?
            "<img title='Zugang barrierefrei' src='../_templates/systemicons/barrierefrei_zugang.jpg' alt='Zugang barrierefrei' width='25' height='25'>" : "") ."
      " . ($team->getZusatzfeldBoolean(SED_utf8_decode('Verfügt das Spiellokal über eine Behindertentoilette?')) ?
            "<img title='WC barrierefrei' src='../_templates/systemicons/barrierefrei_wc.jpg' alt='WC barrierefrei' width='25' height='25'>" : "") ."
            <a href='$stadtplan' target='_blank'>Stadtplan</a>, <a href='$routenplaner' target='_blank'>Routenplaner</a></span><br />
            <br />
            <b>Mannschaftsf&uuml;hrer:</b><br />
            ".$team->get("mf_name");
    if ($prefs['startjahr'] >= date('Y') - 1) {
      if (trim($team->get("mf_telefon"))) echo "<br />Tel.: ".$team->get("mf_telefon");
      if (trim($team->get("mf_telefon2"))) echo "<br />Tel.: ".$team->get("mf_telefon2");
      echo "<br />Email: $email";
    }
    echo "<br />";
    echo "<br></div><div class='col-12 col-md-7'>";

    // Ausgabe Ergebnisse
    if ( $spielplan = $team->getSpielplan () )
    {
        echo "<span class='sed_hl2'>Spielplan</span><br /><br />";
        echo "<table cellspacing='0' cellpadding='3' class='sed_tabelle'>";
        echo "<tr><th>R</th><th>Tag</th><th>H/G</th><th>Gegner</th><th>Erg.</th></tr>";
        foreach ( $spielplan as $spiel )
        {
            echo "<tr><td><a href='?staffel=$spiel[staffel]&amp;r=$spiel[spieltag]'>$spiel[spieltag]</a></td>";
            echo "<td><a href='?staffel=".$team->get("staffel")."&amp;r=$spiel[spieltag]'>$spiel[datum]</a></td><td>$spiel[heim]</td><td>";
            echo SED_TeamLink ( $spiel ['gegner'] );
            echo "</td><td><a href='?staffel=".$team->get("staffel")."&amp;r=$spiel[spieltag]'>".SED_Ergebnis ( $spiel["ergebnis"] )."</a></td></tr>";
        }
        echo "</table>";
    }

    echo "</div></div>";

    // Tabellenkopf der Aufstellung ausgeben
    $staffel = $team->get("staffel");
    $rundenzahl = $staffel ? SED_GetLetzteRunde ( $staffel ) : $prefs['runden'];
    echo "<br /><span class='sed_hl2'>Aufstellung</span>";
    echo "<div class='overflow-auto'>";
    ?>
      <div class="custom-control custom-switch d-sm-none mb-1">
        <input type="checkbox" class="custom-control-input" id="ergSwitch" onclick="$(this).parent().parent().toggleClass('nsv-details-show')">
        <label class="custom-control-label" for="ergSwitch">Einzelergebnisse anzeigen</label>
      </div>
    <?

    echo "<table cellspacing='0' cellpadding='3' class='sed_tabelle'>";
    echo "<tr><th style='border-bottom-width: 2px;'>Nr.</th><th style='border-bottom-width: 2px;'>Name</th><th style='border-bottom-width: 2px; border-right-width: 2px;'>DWZ</th>";
    for ( $r = 1; $r <= $rundenzahl; ++$r )
        echo "<th style='border-bottom-width: 2px;' class='nsv-details'>&nbsp;<a href='?staffel=".$team->get("staffel")."&r=$r'>$r</a>&nbsp;</th>";
    echo "<th style='border-bottom-width: 2px; border-left-width: 2px;'>Pkt</th><th style='border-bottom-width: 2px;'>Sp.</th><th style='border-bottom-width: 2px;'>%</th></tr>";

    // Die Spieler ausgeben
    $ergebnisse = $team->getErgebnisse ();
    foreach ( $team->getAufstellung () as $spieler )
    {
        // Name, DWZ etc.
        $g = $spieler ['istGastspieler'] ? " <span style='font-size:x-small'>(G)</span>" : "";
        echo "<tr><td class='r'>$spieler[brettnr]</td>
            <td class='l text-nowrap'><a href='?spieler=$spieler[id]' title='Zu den Spielerdetails'>".SED_Spielername($spieler)."</a> $g</td>
            <td style='border-right-width: 2px;'>$spieler[dwz]</td>";

        // Einzelergebnisse ausgeben
        for ( $r = 1; $r <= $rundenzahl; ++$r )
        {
            // Wie viele Partien gab es an dem Spieltag?
            if ( !isset ( $ergebnisse [$spieler["id"]][$r] ) )
                echo "<td class='nsv-details'>&nbsp;</td>";
            else {
                // Links zu den Gegnern generieren
                $partien = array ();
                foreach ( $ergebnisse [$spieler["id"]][$r] as $partie )
                    if ( isset ( $partie ["ersatz"] ) )
                        $partien [] = "<a href='?staffel=$partie[staffel]&r=$partie[runde]#p$partie[ersatz]x$partie[gegner]' title='f&uuml;r ".$globals['teams'][$partie["ersatz"]]." ($partie[ergebnis])'>&uarr;</a>";
                    else
                        $partien [] = "<a href='?spieler=$spieler[id]' title='".htmlspecialchars($partie["gegner"], ENT_COMPAT | ENT_HTML401 , 'ISO-8859-1')." (".$globals["teams"][$partie["mannschaft"]].", DWZ ".(int)$partie["dwz"].")'>$partie[ergebnis]</a>";

                // Ausgabe
                echo "<td class='nsv-details'>".implode ( "<br />", $partien )."</td>";
            }
        }

        // Summe etc. ausgeben
        if ( isset ( $ergebnisse [$spieler["id"]]['pkt'] ) )
        {
            echo "<td style='border-left-width: 2px;'>".SED_Ergebnis ( $ergebnisse [$spieler["id"]]["pkt"] )."</td>";
            echo "<td>".$ergebnisse [$spieler["id"]]["spiele"]."</td>";
            echo "<td>".$ergebnisse [$spieler["id"]]["prozent"]."</td>";
            echo "</tr>";
        }
        else
            echo "<td style='border-left-width: 2px;'>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td></tr>";
    }
    echo "</table>";
    echo "</div>";
?>
