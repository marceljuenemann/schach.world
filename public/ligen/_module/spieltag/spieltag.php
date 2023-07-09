<?
/* Spieltag-Anzeige
 *
 * Zeigt Paarungen und Tabelle eines bestimmten Spieltages einer
 * bestimmten Staffel an.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage spieltag
 */

    require_once ( "turnier.inc.php" );
    require_once ( "spieltag.inc.php" );
    require_once ( "tabelle.inc.php" );
    require_once ( "runde.inc.php" );

    // Wurde das Turnier z.B. umbenannt?
    if ( !isset ( $globals ['staffeln'][$_GET ['staffel']] ) )
    {
        require_once ( "gui.inc.php" );
        $staffel = (int) $_GET['staffel'];
        if ($staffel != $_GET['staffel']) exit();
        $tname = reset ( mysql_fetch_array ( mysql_query ( "SELECT t.directory FROM staffeln s INNER JOIN turniere t ON t.id=s.turnier WHERE s.id='$staffel'", $globals ['db'] ), MYSQL_ASSOC ) );
        echo "<b>Sie werden weitergeleitet...</b> $staffel $tname";
        echo "<meta http-equiv='refresh' content='0;URL=$globals[httppath]$tname/?staffel=$_GET[staffel]&r=$_GET[r]' />";
        exit;
    }

    // Welcher Ausgabetyp?
    if ( isset ( $_GET ['ausgabe'] ) )
    {
        $_GET ['ausgabe'] = strtolower ( $_GET ['ausgabe'] ); // wegen PDF im Spieltag-Auswahl-Formular
        if ( !in_array ( $_GET ['ausgabe'], array ( "pdf", "txt", "phparray" ) ) )
            SED_Error ( "Nicht unterstütztes Ausgabeformat!", true );
        $fn = "$globals[basedir]/_module/spieltag/".str_replace ( "..", "-", strtolower ( $_GET ['ausgabe'] ) ).".php";
        if ( file_exists ( $fn ) )
        {
            require_once ( $fn );
            exit;
        }
    }

    require_once ( "gui.inc.php" );

    /////////////////////////////////
    // Vorbereitungen
    /////////////////////////////////

    $_ac                = "text-align: center;";
    $_al                = "text-align: left;";
    $_ar                = "text-align: right;";
    $_bgclr             = "";
    $_br                = "border-right: none;";
    $_bl                = "border-left: none;";
    $_bt                = "border-top: none;";
    $_bb                = "border-bottom: none;";
    $_class             = "sed_tabelle";

    $staffel           = 0;
    $daten             = 0;
    $tabelle           = 0;
    $rundenzahl        = SED_GetLetzteRunde ( $_GET ['staffel'] );
    $kreuztabelle       = true;



    /////////////////////////////////
    // Datenabfrage
    /////////////////////////////////

    // Informationen und Einstellungen über die Staffel abfragen
    $staffel = mysql_fetch_array ( mysql_query ( "SELECT * FROM viewStaffeln WHERE id=$_GET[staffel]", $globals ['db'] ), MYSQL_ASSOC );
    if ( !is_array ( $staffel ) )
        SED_Error ( "Die Staffel scheint nicht zu existieren!", true );

    // Paarungsdaten abfragen
    if ( !Spieltag ( $globals ['tid'], $staffel ['id'], $_GET ['r'], $daten, true, false ) )
        SED_Error ( "Der Spieltag scheint nicht zu existieren!", true );

    // Keine Kreuztabelle anzeigen?
    // TODO: doesn't seem to work anymore?
    if ( count ( $daten ['paarungen'] ) > 6 )
        $kreuztablle = false;
    if ( $staffel ['id'] == 807 ) $kreuztabelle = false; // hack

    // Tabelle abfragen
    if ( $staffel ['showTabelle'] )
    {
        $tabelle = Tabelle ( $staffel ['id'], $_GET ['r'], $kreuztabelle );
    }


    /////////////////////////////////
    // Druckversion & Mehr Optionen
    /////////////////////////////////

    function EchoIcons ( $unten, $daten )
    {
        global $globals, $prefs, $rundenzahl;
        if ( !$unten )
        {
            // Erste Zeile: Druckversion  & Mehr Optionen
            echo "<div class='sed_only_screen d-print-none' style='text-align: right; margin-bottom: 10px'>";
            echo "&nbsp;&nbsp;&nbsp;<a href='Rundschreiben/$_GET[staffel]/Runde$_GET[r].pdf' target='_blank' style='text-decoration: none; color: #777777;'><img alt='' src='$globals[systemicons]printer.gif' /></a>";
            echo "&nbsp;&nbsp;&nbsp;<a href='?staffel=$_GET[staffel]&r=spielplan'><img style='border: none' alt='Spielplan' src='$globals[systemicons]spielplan.gif' /></a>";
            echo "&nbsp;&nbsp;&nbsp;<a href='?staffel=$_GET[staffel]&r=statistik'><img style='border: none' alt='Statistiken' src='$globals[systemicons]statistiken.gif' /></a>";
            if ( $_GET ['r'] > 1 ) echo "&nbsp;&nbsp;&nbsp;<a href='?staffel=$_GET[staffel]&amp;r=".($_GET ['r']-1)."'><img style='border: none' alt='Vorheriger Spieltag' src='$globals[systemicons]pre.png' /></a>";
            if ( $_GET ['r'] < $rundenzahl ) echo "&nbsp;&nbsp;&nbsp;<a href='?staffel=$_GET[staffel]&amp;r=".($_GET ['r']+1)."'><img alt='N&auml;chster Spieltag' src='$globals[systemicons]next.png' style='border: none' /></a>";
          echo "</div>";
        }
        else // Icons unten
        {
            echo "<div class='sed_only_screen d-print-none'><br /><br />";

            // Icons ausgeben: Vorheriger, Druckversion, Nächster
            if ( $_GET ['r'] > 1 ) echo "<img style='vertical-align: text-top;' alt='' src='$globals[systemicons]pre.png' /> <a href='?staffel=$_GET[staffel]&amp;r=".($_GET ['r']-1)."' style='text-decoration: none; color: #777777;'>Vorheriger Spieltag</a>&nbsp;&nbsp;&nbsp;";
            echo "<img style='vertical-align: text-top;' alt='' src='$globals[systemicons]printer.gif' /> <a href='Rundschreiben/$_GET[staffel]/Runde$_GET[r].pdf' target='_blank' style='text-decoration: none; color: #777777;'>Druckversion</a>";
            if ( $_GET ['r'] < $rundenzahl ) echo "&nbsp;&nbsp;&nbsp;<img alt='' src='$globals[systemicons]next.png' style='vertical-align: text-top;' /> <a href='?staffel=$_GET[staffel]&amp;r=".($_GET ['r']+1)."' style='text-decoration: none; color: #777777;'>N&auml;chster Spieltag</a>";

            // Icons ausgeben: Spielplan, Statistik, Als Textdatei
            echo "<br /><img style='vertical-align: text-top;' alt='' src='$globals[systemicons]spielplan.gif' /> <a href='?staffel=$_GET[staffel]&r=spielplan' style='text-decoration: none; color: #777777;'>Spielplan</a>";
            echo "&nbsp;&nbsp;&nbsp;<img style='vertical-align: text-top;' alt='' src='$globals[systemicons]statistiken.gif' /> <a href='?staffel=$_GET[staffel]&r=statistik' style='text-decoration: none; color: #777777;'>Statistiken</a>";
            echo "&nbsp;&nbsp;&nbsp;<img style='vertical-align: text-top;' alt='' src='$globals[systemicons]txt.gif' /> <a href='?staffel=$_GET[staffel]&r=$_GET[r]&ausgabe=txt' style='text-decoration: none; color: #777777;'>Als Textdatei</a>";
            if ( $daten ["timestamp"] )
                echo "&nbsp;&nbsp;&nbsp;<img style='vertical-align: text-top;' alt='' src='$globals[systemicons]timestamp.png' /> <span style='text-decoration: none; color: #777777;'>".date("d.m.Y H:i",($daten["timestamp"]))."</span>";

            echo "</div>";
        }
    }
    EchoIcons ( false, $daten );


    /////////////////////////////////
    // Überschrift & Spiele
    /////////////////////////////////

    // Überschrift
    echo "<div style='margin-left:auto;margin-right:auto'>";
    echo "<div style='text-align: center; line-height: 115%; font-size: large'>$daten[turniername]</div>";
    echo "<div style='text-align: center; line-height: 115%; font-size: x-large; font-weight: bold'>$daten[staffelname]</div>";
    echo "<div style='text-align: center; line-height: 115%; font-size: large'>$_GET[r]. Spieltag - $daten[datum]</div>";
    echo "<br />";
    echo "</div>";

    if (SED_IsNsv2020()) {
      require_once("$globals[basedir]/_module/spieltag/paarungen-nsv2020.php");
    } else {
      require_once("$globals[basedir]/_module/spieltag/paarungen-legacy.php");
    }

    /////////////////////////////////
    // Tabelle
    /////////////////////////////////
    if ( $staffel ['showTabelle'] && count ( $tabelle ) > 1 )
    {
        // Vorbereitungen
        global $_class, $_al, $_ac, $_ar, $_bgclr;
        $_bbl = $kreuztabelle ? "border-left-width: 2px;" : "";
        $_bbb = $kreuztabelle ? "border-bottom-width: 2px;" : "";
        echo "<div class='d-flex justify-content-center'><div class='d-block overflow-auto'>";
        echo "<table style='margin-left:auto;margin-right:auto;' class='$_class' cellspacing='0' cellpadding='2'>";

        // Überschriften
        echo "<tr>";
        for ( $i = 0; $i < count ( $tabelle [0] ); ++$i )
        {
            $b = ( $i == 2 || $i ==  count ( $tabelle [0] ) - 2 ? "$_bbl" : "" );
            $responsive = ($i >= 2 && $i < count($tabelle[0]) - 2) ? "nsv-details" : "";
            echo "<th style='$b $_bgclr $_bbb' class='$responsive'>".$tabelle [0][$i]."</th>";
        }
        echo "</tr>";

        // Daten
        for ( $i = 1; $i < count ( $tabelle ); ++$i )
        {
            echo "<tr>";
            for ( $j = 0; $j < count ( $tabelle [$i] ) - 1; ++$j )
            {
                // Rahmenstärke berechnen
                $a = ( $j == 1 ? $_al : ( $j == 0 ? $_ar : $_ac ) );
                $b = ( $j == 2 || $j ==  count ( $tabelle [$i] ) - 3 ? "$_bbl" : "" );

                // CSS-Klasse berechnen (Auf- und Absteiger)
                $class = ( $j == 0 ? "sed_".$tabelle [$i][count($tabelle [$i])-1] : "" );

                // Details?
                $responsive = ($j >= 2 && $j < count($tabelle[0]) - 2) ? "nsv-details" : "";
              
                // Ggf. Links erzeugen
                $html = "";
                $cell = $tabelle [$i][$j];
                if ( is_array ( $cell ) ){
                    if ( isset ( $cell ["url"] ) )
                        $html = "<a href='$cell[url]' title='$cell[title]'>$cell[text]</a>";
                    else
                        foreach ( $cell as $spiel ){
                            if ( $html != "" ) $html .= "<br />";
                            $html .= "<a href='$spiel[url]' title='$spiel[title]'>$spiel[text]</a>";
                        }
                } else {
                    $html .= $cell;
                }

                // Spielt die Mannschaft gegen sich selbst?
                if ( $j == $i + 1 && $kreuztabelle )
                    echo "<td style='background-color: #dddddd; color: #dddddd; $a $b' class='$responsive'>$html</td>";
                else
                    echo "<td style='$a $b' class='$class $responsive text-nowrap'>".$html."</td>";
            }
            echo "</tr>";
        }

        // Ende
        echo '</table>';
        if ($kreuztabelle && SED_IsNsv2020()) {
          ?>
            <div class="custom-control custom-switch d-sm-none mt-1 mb-2">
              <input type="checkbox" class="custom-control-input" id="kreuztabelleSwitch" onclick="$(this).parent().parent().toggleClass('nsv-details-show')">
              <label class="custom-control-label" for="kreuztabelleSwitch">Kreuztabelle</label>
            </div>
          <?
        }
        echo '</div></div><br />';
    }


    /////////////////////////////////
    // Infos
    /////////////////////////////////
    $content = "";

    // Bemerkungen
    if ( isset ( $daten ['bemerkung'] ) && $daten ['bemerkung'] ){
        $content .= "<b>Bemerkungen</b><br />";
        $content .= nl2br ( $daten ['bemerkung'] )."<br />";
    }

    // Nachmeldungen
    if ( count ( $daten ['nachmeldungen'] ) && $staffel ['showNachmeldungen'] )
    {
        $lastteam = "";
        foreach ( $daten ['nachmeldungen'] as $nachmeldung ) {
            // Für jede Mannschaft eine Überschrift ausgeben
            if ( $nachmeldung ['mannschaft'] != $lastteam )
            {
                $lastteam = $nachmeldung ['mannschaft'];
                $content .= "<BR><B>Nachmeldung $lastteam</B><BR>";
            }

            $berechtigtAb = $nachmeldung ['berechtigtAb'] == $_GET ['r'] ? "" : " (ab $nachmeldung[berechtigtAb]. Spieltag)";

            // Nun den Spieler ausgeben
            if ( $staffel ['showPassNr'] )
                $content .= "$nachmeldung[passnr] ";
            $content .= "<a href='?spieler=$nachmeldung[id]' style='text-decoration:none'>$nachmeldung[fullname]</a>$berechtigtAb<BR>";
        }
    }

    // Spieltag Vorschau
    if ( $staffel ['showSpieltagvorschau'] && $daten ['vorschau'] )
    {
        $content .= "<br /><b><a href='?staffel=$_GET[staffel]&amp;r=".($_GET['r']+1)."' style='color: #000000; text-decoration: none;'>N&auml;chster Spieltag (".$daten ['vorschautermin'].")</a></b><br />";
        $xtra = "";
        foreach ( $daten ['vorschau'] as $paarung )
        {
            $content .= "$xtra$paarung[mannschaft1] - $paarung[mannschaft2]";
            $content .= $paarung['verlegung'] ? " ($paarung[verlegung])" : '';
            $xtra = "<br />";
        }
    }

    // Staffelleiter ausgeben
    $content .= sprintf ( "<br /><br /><b>Staffelleiter</b><br />%s<br />Tel.: %s<br />%s", $daten ['sl_name'], $daten ['sl_telefon'], SED_Email ( $daten ['sl_email'] ) );

    // Ausgabe
    echo "<div style='text-align: left;'>$content</div>";
    EchoIcons ( true, $daten );
?>
