<?
/* Spieltag-Anzeige als PDF
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage spieltag
 */
    // Includes
    require_once ( "extern/fpdf.php" );
    require_once ( "extern/fpdf.html.php" );

    // Daten laden
    $data = array ();
    if ( !Spieltag ( $globals ['tid'], $_GET ['staffel'], $_GET ['r'], $data ) )
        SED_Error ( "Fehler beim Laden der Spieltag-Daten.", true );
    $table = Tabelle ( $_GET ['staffel'], $_GET ['r'], true );
    $options = mysql_fetch_array ( mysql_query ( "SELECT * FROM viewStaffeln WHERE id=$_GET[staffel]", $globals ['db'] ), MYSQL_ASSOC );
    $urlbase = $globals ['httppath'] . $prefs ['directory'] . '/';


    // Logik, damit alles auf eine Seite passt
    // tmp = (fontsize,lineheight,kreuztabelle)
    $linecount = count ( $data ['paarungen'] ) * ( SED_GetBrettzahl ( $_GET ['staffel'] ) + 2 );
        if ( $linecount <= 32 )      $tmp = array ( 10,5,1 );
        elseif ( $linecount <= 40 )  $tmp = array ( 10,4.5,1 );
        elseif ( $linecount <= 50 )  $tmp = array ( 10,4,1 );
        elseif ( $linecount <= 54 )  $tmp = array ( 10,4.5,0 );
        elseif ( $linecount <= 60 )  $tmp = array ( 10,4,0 );
        elseif ( $linecount <= 70 )  $tmp = array ( 9,3.5,0 );
        elseif ( $linecount <= 75 )  $tmp = array ( 9,3.5,0 );
        else                         $tmp = array ( 10,4,1 );
    $fontsize = $tmp [0];
    $cellheight = $tmp [1];
    $isBigTable = count ( $table ) > 13 ? 0 : $tmp [2];


    // Hintergrundfarbe festlegen
    $bgcolors = array (
        "aufsteiger" => "C5FFA1",
        "absteiger" => "FFD1CF",
        "aufsteigerRelegation" => "FFF8AB",
        "absteigerRelegation" => "FFE9B8",
        "gray" => "DDDDDD"
    );
    function SetFillColor ( $pdf, $name )
    {
        global $bgcolors;
        if ( isset ( $bgcolors [$name] ) )
            $pdf->SetFillColor (
                base_convert( substr($bgcolors[$name], 0, 2), 16, 10 ),
                base_convert( substr($bgcolors[$name], 2, 2), 16, 10 ),
                base_convert( substr($bgcolors[$name], 4, 2), 16, 10 ) );
        else
            $pdf->SetFillColor ( 255, 255, 255 );
    }

    // Für schönere Bemerkungs-Anmerkungen (hochgestellte 1, 2, 3)
    $bemerkungsSternchen = array ( 0, chr(0xB9), chr(0xB2), chr(0xB3), '*' );
    $bemerkungen = array (); // mit tupel ( char bemerkung )

    // PDF vorbereiten
    $pdf = new FPDF_HTML ();
    $pdf->InitHTML ();
    SetFillColor ( $pdf, "gray" );
    $pageWidth = 210;
    $pdf->AddPage ();
    $pdf->SetLeftMargin ( 10 );
    $pdf->SetRightMargin ( 7 );
    $pdf->SetAutoPageBreak(true, 15);

    // Schriftart Tahoma aktivieren
    $pdf->AddFont ( "tahoma" );
    $pdf->AddFont ( "tahoma", "B" );
    $pdf->AddFont ( "tahoma", "I" );

    // Berechnung der Breite der Tabelle
    $paddingTable = 3;
    $pdf->SetFont ( "tahoma", "", $fontsize );
    $table_stdcolwidth = $pdf->GetStringWidth ( "XXX" );
    $table_colwidth[0] = count($table)>10 ? $pdf->GetStringWidth ( "10." ) : $pdf->GetStringWidth ( "8." );
    $table_colwidth[1] = $pdf->GetStringWidth ( "Torgelow-Drogeheide  " );
    $tablewidth = array_sum ( $table_colwidth ) + 2 * $table_stdcolwidth + $paddingTable * 4;

    // Berechnung der Breite der rechten Spalte
    $pdf->SetFont ( "tahoma", "", 9 );
    $infowidth = $pdf->GetStringWidth ( "SG Oesede-Gmth. 3 - SG Oesede-Gmth. 3" );
    $columnwidth = ( $isBigTable || !$options ['showTabelle'] )
                 ? $infowidth : max ( $infowidth, $tablewidth );


    ///////////////////////////////////
    // Überschrift ausgeben
    {
        // Daten sammeln
        $headlines = array ( $data ['turniername'], $data ['staffelname'], $_GET['r'] . ". Spieltag - " . $data ['datum'] );

        // Schriftgrößen und -dekorationen
        $hl_sizes = array ( 12, 16, 12 );
        $hl_heights = array ( 5, 7, 4 );
        $hl_styles = array ( "", "B", "" );

        // Ausgabe der Zeilen
        for ( $i = 0; $i < 3; ++$i )
        {
            $pdf->SetFont ( "tahoma", $hl_styles [$i], $hl_sizes [$i] );
            $pdf->Cell ( 0, $hl_heights [$i], $headlines [$i], 0, 1, "C", false );
        }
        $pdf->SetY ( $pdf->GetY () + 3 );
    }
    $columnY = $pdf->GetY ();

    ///////////////////////////////////
    // Paarungen ausgeben
    $widthOfGames = $pageWidth - 2*10 - $columnwidth - 3;
    {
        // Spaltenbreiten berechnen
        $pdf->SetFont ( "", "B", $fontsize );
        $width[0] = $options ['showPassNr']
                  ? ( $prefs['spielDreistelligeNr'] ? $pdf->GetStringWidth ( "616" ) : $pdf->GetStringWidth ( "24" ) ) + 3
                  : 0;
        $width[2] = $pdf->GetStringWidth ( "(2999)" );
        $width[3] = $pdf->GetStringWidth ( '-8' . SED_REMIS );
        $width[4] = $pdf->GetStringWidth ( ":" );
        $width[1] = ( $widthOfGames - 2*array_sum ( $width ) ) / 2;
        $widthOfGames = array_sum ( $width ) * 2 - $width [4];

        // Spaltenbreiten für Überschriften
        $widthHead = $width;
        $widthHead[0] += $widthHead[1]+$widthHead[2];
        $widthHead[1] = $widthHead[2] = 0;

        // Styles der Spalten
        $borders = array ( 1, "LTB", "TB", "LTB", "TB", "RTB", "TB", "RTB", 1 );
        $aligns = array ( "C", "L", "R", "C", "C", "C", "L", "R", "C" );
        $alignsHead = array ( "L", "L", "R", "C", "C", "C", "L", "R", "R" );
        $fontsizes = array ( 0, 0, $fontsize-2, 0, 0 );

        // Begegnungen ausgeben
        for ( $i = 0; $i < count ( $data ['paarungen'] ); ++$i )
        {
            // Bemerkungs-Sternchen
            $sternchen = "";
            if ( $data ['paarungen'][$i]['bemerkung'] ){
                $sternchen = next ( $bemerkungsSternchen );
                if ( !$sternchen ) $sternchen = "*";
                $bemerkungen [] = array ( $sternchen, $data ['paarungen'][$i]['bemerkung'] );
            }

            // Inhalte
            $contents = array (
                $data ['paarungen'][$i]['m1'], "", "",
                $data ['paarungen'][$i]['erg1'], ":",
                $data ['paarungen'][$i]['erg2'] . $sternchen,
                "", "", $data ['paarungen'][$i]['m2']
            );

            // Links
            $links = array (
                $urlbase . "?mannschaft=" . $data ['paarungen'][$i]['mid1'],
                "", "", "", "", "", "", "",
                $urlbase . "?mannschaft=" . $data ['paarungen'][$i]['mid2']
            );

            // Wirklich ausgeben
            $pdf->SetFont ( "", "B", $fontsize );
            for ( $t = 0; $t <= 8; ++$t )
                if ( $widthHead[$t<5?$t:8-$t] )
                    $pdf->Cell ( $widthHead[$t<5?$t:8-$t], $cellheight, $contents[$t], $borders[$t], 0, $alignsHead[$t], 1, $links[$t] );
            $pdf->Ln();

            // Verlegung & Ausrichter
            if ( count ( $data ['paarungen'][$i]['paarungen'] ) == 0 )
            {
                $pdf->SetFont ( "", "", $fontsize );
                if ( $data ['paarungen'][$i]['datum'] != $data ['datum'] )
                    $pdf->Cell ( $widthOfGames, $cellheight, "Findet statt am ".$data ['paarungen'][$i]['datum'], 1, 1, "C", 0 );
                if ( $data ['paarungen'][$i]['ausrichterId'] != $data ['paarungen'][$i]['mid1'] )
                    $pdf->Cell ( $widthOfGames, $cellheight, "Ausgerichtet von: ".$data ['paarungen'][$i]['ausrichter'], 1, 1, "C", 0 );
            }

            // kampflos gewonnen?
            elseif ( $data ['paarungen'][$i]['kampflos'] ){
                $pdf->SetFont ( "", "", $fontsize );
                $pdf->Cell ( $widthOfGames, $cellheight, "(kampflos)", 1, 1, "C", 0 );
            }

            // Spielerpaarungen
            else
                // Spielerpaarungen ausgeben
                for ( $j = 0; $j < count ( $data ['paarungen'][$i]['paarungen'] ); ++$j )
                {
                    // Inhalte
                    $contents = array (
                        $data ['paarungen'][$i]['paarungen'][$j]['s1pass'],
                        $data ['paarungen'][$i]['paarungen'][$j]['s1fullname'],
                        $data ['paarungen'][$i]['paarungen'][$j]['dwz1']==""?"":("(".$data ['paarungen'][$i]['paarungen'][$j]['dwz1'].")"),
                        $data ['paarungen'][$i]['paarungen'][$j]['erg1'],
                        ":",
                        $data ['paarungen'][$i]['paarungen'][$j]['erg2'],
                        $data ['paarungen'][$i]['paarungen'][$j]['dwz2']==""?"":("(".$data ['paarungen'][$i]['paarungen'][$j]['dwz2'].")"),
                        $data ['paarungen'][$i]['paarungen'][$j]['s2fullname'],
                        $data ['paarungen'][$i]['paarungen'][$j]['s2pass']
                    );

                    // Links
                    $links = array (
                        "",
                        $urlbase . "?spieler=" . $data ['paarungen'][$i]['paarungen'][$j]['sid1'],
                        "", "", "", "", "",
                        $urlbase . "?spieler=" . $data ['paarungen'][$i]['paarungen'][$j]['sid2'],
                        ""
                    );

                    // Wirklich ausgeben
                    for ( $t = 0; $t <= 8; ++$t )
                    {
                        // Soll die Zelle nicht ausgegeben werden?
                        $u = $t<5 ? $t : 8-$t;
                        if ( $width [$u] == 0 ) continue;

                        // Schriftgröße auf Standart, bei DWZ -2
                        $pdf->SetFont ( "", "", $fontsizes[$u] ? $fontsizes[$u] : $fontsize );

                        // Passt der Name?
                        if ( $u == 1 ) // Es ist ein Namensfeld
                            for ( $k = -1; $pdf->GetStringWidth ( $contents[$t] ) > $width[$u] && $k > -10; --$k )
                                $pdf->SetFont ( "", "", $fontsize + $k );

                        // Wirklich wirklich ausgeben
                        $pdf->Cell ( $width[$u], $cellheight, $contents[$t], $borders[$t], 0, $aligns[$t], 0, $links[$t] );
                    }
                    $pdf->Ln();
                }
            $pdf->Ln();
        }
    }

    // evtl. Spaltenwechsel
    if ( !$isBigTable )
    {
        $pdf->SetLeftMargin ( $pageWidth - 10 - $columnwidth );
        $pdf->SetY ( $columnY );
    }


    ///////////////////////////////////
    // Tabelle
    if ( $options ['showTabelle'] && isset($table[0]) )
    {
        // Überschriften
        $pdf->SetFont ( "", "B", $fontsize );
        for ( $i = 0; $i < count ( $table[0] ); ++$i )
        {
            // Einzelergebnisse nicht ausgeben?
            if ( $isBigTable == false && $i > 1 && $i < count ( $table[0] )-2 )
                continue;

            // evtl. ein etwas dickerer Rahmen
            if ( $isBigTable && ( $i == 2 || $i == count ( $table[0] ) - 2 ) )
              $pdf->SetX ( $pdf->GetX () + .2 );

            // Ausgaben
            $cellwidth = isset ( $table_colwidth [$i] ) ? $table_colwidth [$i] : $table_stdcolwidth;
            $pdf->Cell ( $cellwidth + $paddingTable, $cellheight, $table[0][$i], 1, 0, "C", 1 );
        }
        $pdf->Ln ();

        // Dicken Rahmen
        if ( $isBigTable )
            $pdf->SetY ( $pdf->GetY () + .2 );

        // Zeile ausgeben
        $pdf->SetFont ( "", "", $fontsize );
        for ( $i = 1; $i < count ( $table ); ++$i )
        {
            // Hintergrundfarbe festlegen
            SetFillColor ( $pdf, $table [$i][count($table[$i])-1] );

            // Sonderbehandlung für Doppelrundige Turniere
            $maxHeight = 1;
            if ( $isBigTable )
                for ( $j = 2; $j < count ( $table [$i] ) - 3; ++$j )
                    if ( is_array ( $table [$i][$j] ) )
                        $maxHeight = max ( $maxHeight, count ( $table [$i][$j] ) );
            $rowHeight = $maxHeight * $cellheight;

            // Zellen ausgeben
            for ( $j = 0; $j < count ( $table[$i] ) - 1; ++$j )
            {
                $content = $table[$i][$j];

                // Einzelergebnisse evlt. nicht ausgeben!
                if ( $isBigTable == false && $j > 1 && $j < count ( $table[$i] )-3 )
                    continue;

                // evtl. ein etwas dickerer Rahmen
                if ( $isBigTable && ( $j == 2 || $j == count ( $table[$i] )-3 ) )
                    $pdf->SetX ( $pdf->GetX () + .2 );

                // Soll der Hintergrund gefärbt werden?
                $fill = ( $j == 0 || ( $isBigTable && $j == $i + 1 ) );

                // Sonderbehandlung für xxx-Zellen
                if ( $fill && $j > 0 )
                {
                    SetFillColor ( $pdf, "gray" );
                    $content = "";
                }

                // Wenn es kein Ergebnis ist
                if ( !is_array ( $content ) || isset ( $content ["url"] ) ){
                    $cellwidth = isset ( $table_colwidth [$j] ) ? $table_colwidth [$j] : $table_stdcolwidth;
                    $align = ( $j == 0 ? "R" : ( $j == 1 ? "L" : "C" ) );
                    $pdf->Cell ( $cellwidth + $paddingTable, $rowHeight, is_array($content)?"$content[text]":$content, 1, 0, $align, $fill, is_array($content)?"$urlbase$content[url]":"" );
                } else {
                    // Bei mehreren Ergebnissen jedes in einer Zeile
                    $cellwidth = isset ( $table_colwidth [$j] ) ? $table_colwidth [$j] : $table_stdcolwidth;
                    $x = $pdf->GetX ();
                    $y = $pdf->GetY ();

                    // Zunächst zeichnen wir explizit den Rahmen
                    $pdf->Cell ( $cellwidth + $paddingTable, $rowHeight, " ", 1, 2, "C", $fill );
                    $pdf->SetY ( $y );
                    $pdf->SetX ( $x );

                    // Die Ergebnisse haben nun die normale cellheight
                    foreach ( $content as $spiel ){
                        $pdf->Cell ( $cellwidth + $paddingTable, $cellheight, "$spiel[text]", 0, 2, "C", $fill, "$urlbase$spiel[url]" );
                    }

                    // Den Zeiger auf die nächste Zelle setzen
                    $pdf->SetY ( $y );
                    $pdf->SetX ( $x + $cellwidth + $paddingTable );
                }
            }

            // evtl. gleich mehrere Zeilenumbrüche
            $pdf->Ln ();
        }
        $pdf->Ln ();
    }

    // evtl. Spaltenwechsel, aber nicht wenn unter der Tabelle Platz ist
    if ( $isBigTable && $pdf->GetY () > 150 )
    {
        $pdf->SetLeftMargin ( 10 + $widthOfGames + 3 );
        $pdf->SetY ( $columnY );
    }


    ///////////////////////////////////
    // Infos
    {
        // Schrift kleiner
        $pdf->SetFont ( "", "", 9 );

        // Bemerkungen
        $content = "<B>Bemerkungen</B><BR>";
        foreach ( $bemerkungen as $bemerkung )
            $content .= "$bemerkung[0]$bemerkung[1]<BR><BR>";
        if ( isset ( $data ['bemerkung'] ) && $data ['bemerkung'] )
            $content .= str_replace ( "\r\n", "<BR>", str_replace ( "\n", "<BR>", $data ['bemerkung'] ) )."<BR>";

        // Nachmeldungen
        if ( count ( $data ['nachmeldungen'] ) && $options ['showNachmeldungen'] )
        {
            $lastteam = "";
            foreach ( $data ['nachmeldungen'] as $nachmeldung )
                if ( $nachmeldung ['berechtigtAb'] == $_GET ['r'] || $nachmeldung ['berechtigtAb'] == $_GET ['r'] + 1 )
                {
                    // Für jede Mannschaft eine Überschrift ausgeben
                    if ( $nachmeldung ['mannschaft'] != $lastteam )
                    {
                        $lastteam = $nachmeldung ['mannschaft'];
                        $content .= "<BR><B>Nachmeldung $lastteam</B><BR>";
                    }

                    // Nun den Spieler ausgeben
                    if ( $options ['showPassNr'] )
                        $content .= "$nachmeldung[passnr] ";
                    $content .= "<A HREF='$urlbase?spieler=$nachmeldung[id]'>$nachmeldung[fullname]</A>";

                    if ($nachmeldung ['berechtigtAb'] != $_GET ['r'])
                        $content .= " (ab $nachmeldung[berechtigtAb]. Spieltag)<BR>";
                    else
                        $content .= "<BR>";
                }
        }

        // Spieltag Vorschau
        if ( $data ['vorschau'] && $options ['showSpieltagvorschau'] )
        {
            $content .= utf8_decode("<BR><B>Nächster Spieltag (".$data ['vorschautermin'].")</B><BR>");
            $xtra = "";
            foreach ( $data ['vorschau'] as $paarung )
            {
                $content .= "$xtra$paarung[mannschaft1] - $paarung[mannschaft2]";
                $content .= $paarung['verlegung'] ? " ($paarung[verlegung])" : '';
                $xtra = "<BR>";
            }
        }

        // Ausgabe & Zurücksetzen
        $pdf->WriteHTML ( "$content<BR>", $cellheight );
    }


    ///////////////////////////////////
    // Fußzeile
    {
        $pdf->SetFont ( "", "", 10 );
        $pdf->SetAutoPageBreak ( false );
        $pdf->SetLeftMargin ( 10 );
        $pdf->SetY ( -15 );

        // Internetadresse ausgeben
        $text = "Internet: " . $urlbase;
        $pdf->Cell ( 0, 4.5, $text, 0, 1, "C", false, $urlbase );

        // Staffelleiter ausgeben
        $text = sprintf ( "Staffelleiter: %s - Tel.: %s - Email: %s", $data ['sl_name'], $data ['sl_telefon'], $data ['sl_email'] );
        $pdf->Cell ( 0, 4.5, $text, 0, 1, "C" );
    }


    // Endgültige Ausgabe
    $pdf->Output ( $globals ['staffeln'][$_GET ['staffel']]." $_GET[r].pdf", "I" );
?>
