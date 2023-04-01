<?
/* Statistiken
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
    echo "<meta http-equiv='refresh' content='0;URL=$globals[httppath]$tname/?staffel=$_GET[staffel]&r=statistik' />";
    exit;
  }

    // Ganz einfach..
    $brettzahl = SED_GetBrettzahl ( $_GET ['staffel'] );

    // SPIELER INFOS
    // [sortiert nach punkte, partien, brett]
    $spieler = array ();
    $spielerDefault = array ( "name"=>"", "dwz"=>0, "mannschaft"=>0,
        "brett"=>0, "partien"=>0, "punkte"=>0.0, "remis"=>0 );

    // MANNSCHAFTS INFOS
    $teams = array ();
    $teamDefault = array ( "eingesetzte"=>0, "topX"=>0, "alle"=>0, "alter"=>0, "alterAnzahl"=>0,
        "partien"=>0, "-"=>0, "+"=>0, "?"=>0, "1"=>0, "½"=>0, "0"=>0, "W"=>0.0, "S"=>0.0, "WAnzahl"=>0, "SAnzahl"=>0 );

    // REMIS KOENG
    $koenig = array ();

    ////////////////////////////////////////////////
    // Spielerpaarungen abfragen und verarbeiten
    ////////////////////////////////////////////////

    $rsrc = mysql_query ( "SELECT mannschaft1,mannschaft2,ergebnis1,ergebnis2,brett,spieler1,spieler2,s1.dwz dwz1,s2.dwz dwz2,s1.geburt geburt1,s2.geburt geburt2
        FROM paarungen p
        INNER JOIN spielerpaarungen sp ON sp.paarung=p.id
        LEFT JOIN spieler s1 ON s1.id=sp.spieler1
        LEFT JOIN spieler s2 ON s2.id=sp.spieler2
        LEFT JOIN mannschaften m1 ON m1.id=p.mannschaft1
        LEFT JOIN mannschaften m2 ON m2.id=p.mannschaft2
        WHERE p.staffel='$_GET[staffel]'
          AND m1.staffel='$_GET[staffel]'
          AND m2.staffel='$_GET[staffel]'
          AND $brettzahl >= brett", $globals ['db'] );

    // Verarbeiten
    $teamNr = array ( 1, 2 );
    if ( $rsrc ) while ( $partie = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ){
        // Für jede der beiden Mannschaften
        foreach ( $teamNr as $team ){
            // Wichtige Daten leichter verfügbar machen
            $m = $partie ["mannschaft$team"];
            $s = $partie ["spieler$team"];
            $e = $partie ["ergebnis$team"];
            $d = $partie ["dwz$team"];
            $g = $partie ["geburt$team"];

            // Existenz der Mannschaft/Spieler sicherstellen
            if ( !isset ( $teams [$m] ) ) $teams [$m] = $teamDefault;
            if ( !isset ( $spieler [$s] ) ) $spieler [$s] = $spielerDefault;

            // Die ganzen Statistiken führen...
            $farbe = ($partie["brett"]%2 xor $team%2) ? "W" : "S";
            switch ( $e ){
                case "1":
                    $spieler [$s]["punkte"] += 0.5;
                    $teams [$m][$farbe] += 0.5;
                case "½":
                    $spieler [$s]["punkte"] += 0.5;
                    $teams [$m][$farbe] += 0.5;
                case "0":
                    $teams [$m][$farbe."Anzahl"] ++;
                case "+":
                    $teams [$m]['partien'] ++;
                    $teams [$m]['eingesetzte'] += $d ? $d : 700;
                    $spieler [$s]['brett'] += $partie ["brett"];
                    $spieler [$s]["partien"] ++;
                    if ( $e == "+" ) $spieler [$s]["punkte"] += 1.0;
                    if ( $g > 1900 ){
                        $teams [$m]['alter'] += $g;
                        $teams [$m]['alterAnzahl'] ++;
                    }
                case "-":
                    $teams [$m][$e] ++;
            }

            // Remiskönig
            if ( $e == "½" ){
                unset ( $koenig [$spieler [$s]['remis']][$s] );
                $koenig [++$spieler [$s]['remis']][] = $s;
            }
        }
    }

    ////////////////////////////////////////////////
    // Top-Scorer Liste sortieren
    ////////////////////////////////////////////////

    function __cmpSpielerExt ( $a, $b, $crit ){
        global $spieler;
        if ( $spieler [$a][$crit] < $spieler [$b][$crit] ) return -1;
        return $spieler [$a][$crit] > $spieler [$b][$crit];
    }
    function __cmpSpieler ( $a, $b ){
        if ( ($cmp = __cmpSpielerExt ( $a, $b, "punkte" ) )!=0 ) return -$cmp;
        if ( ($cmp = __cmpSpielerExt ( $a, $b, "partien" ) )!=0 ) return $cmp;
        if ( ($cmp = __cmpSpielerExt ( $a, $b, "brett" ) )!=0 ) return $cmp;
        return 0;
    }
    $scorer = array_keys ( $spieler );
    uasort ( $scorer, "__cmpSpieler" );
    $topscorer = array ();
    foreach ( $scorer as $id=>$data ){
        if ( !$data ) continue;
        if ( count ( $topscorer ) >= 10 ) break;
        $topscorer [$id] = $data;
    }

    // Top-Scorer und Remiskönig Namen holen
    $koenig = @reset ( $koenig [max(array_keys($koenig))] );
    foreach ( array_merge ( $topscorer, array ( $koenig ) ) as $id ){
        if ( $id )
            foreach ( SED_MYSQL_Array ( "SELECT id, CONCAT(vorname,' ',nachname) name, dwz, mannschaft FROM spieler WHERE id=$id LIMIT 1" ) as $key=>$value )
                $spieler [$id][$key] = $value;
    }

    ////////////////////////////////////////////////
    // DWZ-Statistik
    ////////////////////////////////////////////////

    // Liefert alle Mannschaften (mnr, avgAlle)
    $rsrc = mysql_query ( "
                          SELECT s.mannschaft mnr, ROUND(AVG(IF(dwz IS NOT NULL and dwz>0,dwz,700))) avgAlle
                          FROM spieler s INNER JOIN mannschaften m ON m.id=s.mannschaft
                          WHERE m.staffel=$_GET[staffel]
                          GROUP BY s.mannschaft
                          ORDER BY avgAlle DESC ", $globals ['db'] );

    // Nun zu jedem topX berechnen
    while ( $mannschaft = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ){
        // Existenz der Mannschaft/Spieler sicherstellen
        $m = $mannschaft ["mnr"];
        if ( !isset ( $teams [$m] ) ) $teams [$m] = $teamDefault;
        $teams [$m]["alle"] = $mannschaft ["avgAlle"];
        $teams [$m]["inStaffel"] = 1;

        // Eingesetzte jetzt richtig
        if ( $teams [$m]["partien"] )
            $teams [$m]["eingesetzte"] = round ( $teams [$m]["eingesetzte"] / $teams [$m]["partien"] );
        else
            $teams [$m]["eingesetzte"] = "";

        // Alter jetzt richtig
        if ( $teams [$m]["alterAnzahl"] )
            $teams [$m]["alter"] = date ( "Y" ) - round ( $teams [$m]["alter"] / $teams [$m]["alterAnzahl"] );
        else
            $teams [$m]["alter"] = "";

        // Top X berechnen
        $topX = reset ( mysql_fetch_array ( mysql_query (
            "SELECT ROUND(AVG(dwz2))
            FROM (
                SELECT IF(dwz IS NOT NULL and dwz>0,dwz,700) as dwz2
                FROM spieler
                WHERE mannschaft=$mannschaft[mnr]
                ORDER BY brettnr
                LIMIT $brettzahl
            ) as a", $globals ['db'] ), MYSQL_NUM ) );
        $teams [$m]["topX"] = $topX;
    }

    // Sortieren
    function __cmpTExt ( $a, $b, $crit ){
        if ( $a[$crit] < $b[$crit] ) return -1;
        return $a[$crit] > $b[$crit];
    }
    function __cmpT ( $a, $b ){
        if ( ($cmp = __cmpTExt ( $a, $b, "eingesetzte" ) )!=0 ) return -$cmp;
        if ( ($cmp = __cmpTExt ( $a, $b, "topX" ) )!=0 ) return -$cmp;
        return 0;
    }
    uasort ( $teams, "__cmpT" );

    ////////////////////////////////////////////////
    // Summen
    ////////////////////////////////////////////////

    $teamSum = array ( "eingesetzte"=>0, "topX"=>0, "alle"=>0, "alter"=>0, "alterAnzahl"=>0,
        "partien"=>0, "-"=>0, "+"=>0, "?"=>0, "1"=>0, "½"=>0, "0"=>0, "W"=>0.0, "S"=>0.0, "WAnzahl"=>0, "SAnzahl"=>0, "inStaffel"=>0 );
    foreach ( $teamSum as $k=>$v ){
        foreach ( $teams as $team=>$data ){
            $teamSum [$k] += $data [$k];
        }
    }

    // Durchschnitte
    $teamAvg = array ( "eingesetzte"=>0, "topX"=>0, "alle"=>0, "alter"=>0 );
    foreach ( $teamAvg as $k=>$v ){
        $divisor = "inStaffel";
        $divisor = $teamSum [$divisor];
        $teamAvg [$k] = $divisor ? round ( $teamSum [$k] / $divisor ) : "";
    }

    // Maximalwerte
    $teamMax = array ( "eingesetzte"=>0, "topX"=>0, "alle"=>0, "alter"=>0 );
    foreach ( $teams as $data )
        foreach ( $teamMax as $id=>$max ){
            if ( $data [$id] > $max && isset ( $data ["inStaffel"] ) )
                $teamMax [$id] = $data [$id];
        }


    /*
    echo "<!--";
		print_r($topscorer);
		print_r($spieler)
    print_r($teams);
    print_r($teamNr);
    print_r($teamSum);
    print_r($teamMax);
    print_r($teamDefault);
    print_r($teamAvg);
    echo "-->";
    */

    ////////////////////////////////////////////////
    // Ausgabe
    ////////////////////////////////////////////////

    // Überschrift
    echo "<span class='sed_hl1'>Statistiken für " . $globals ['staffeln'][$_GET ['staffel']] . "</span><br /><br />";

    // Alter und DWZ
    echo "Ein in dieser Staffel eingesetzter Spieler hat durchschnittlich eine
        <b>DWZ von $teamAvg[eingesetzte]</b> und ist <b>$teamAvg[alter] Jahre</b> alt. ";

    // TopScorer
    $tops = $spieler [reset($topscorer)];
    if ( is_array ( $tops ) )
    echo "Der Top-Scorer mit $tops[punkte] Punkten aus $tops[partien]
        Partien ist <a href='?spieler=$tops[id]'>$tops[name]</a> (".
        SED_TeamLink($tops['mannschaft'])."). ";

    // Remiskönig
    $rk = $spieler [$koenig];
    if ( is_array ( $rk ) )
    echo "Der Remiskönig mit $rk[remis] Remis ist
        <a href='?spieler=$rk[id]'>$rk[name]</a>
        (". SED_TeamLink ( $rk ['mannschaft'] ) ."). ";

    // Spiele
    $sp = (($teamSum['partien']+$teamSum['-'])/2);
    $kp = $sp ? round ( $teamSum ['-'] / $sp *100) : 0;
    $spR = (($teamSum['partien']-$teamSum['+'])/2);
    $r = $teamSum['½'] ? round($teamSum['½']/2/$spR*100) : 0;
    $w = $teamSum['W'] ? round($teamSum['W']/$spR*100) : 0;
    $s = $teamSum['S'] ? round($teamSum['S']/$spR*100) : 0;
    echo $teamSum["-"]." Spiele wurden kampflos verloren gegeben, das sind
        <b>$kp%</b>. Von den $spR wirklich gespielten Partien sind <b>$r% Remis</b>
        ausgegangen. Die Wei&szlig;spieler haben einen Score von <b>$w%</b>,
        der Score von Schwarz ist <b>$s%</b>. ";


    ////////////////////////////////////////////////////////
    // DWZ-Schnitte
    ////////////////////////////////////////////////

    echo "<br /><br /><br /><span class='sed_hl2'>DWZ-Statistik</span><br /><br />";
    $table = array (
        "eingesetzte" => array ( "Eingesetzte", "DWZ Durchschnitt der Spieler, die tats&auml;chlich gespielt haben. Spieler ohne DWZ werden als DWZ 700 gewertet." ),
        "topX" => array ( "&nbsp;&nbsp;Top $brettzahl&nbsp;&nbsp;", "Durchschnittliche DWZ der Stammspieler. Spieler ohne DWZ werden als DWZ 700 gewertet." ),
        "alle" => array ( "Alle Spieler", "Durchschnittliche DWZ von allen gemeldeten Spielern. Spieler ohne DWZ werden als DWZ 700 gewertet." ),
        "alter" => array ( "Alter &empty;", "Durchschnittliches Alter der Spieler, die tats&auml;chlich gespielt haben" )
    );

    // Tabellenkopf
    echo "<table cellpadding='3' class='sed_tabelle'><tr><th>Mannschaft</th>";
    foreach ( $table as $col )
        echo "<th><a title='$col[1]' href='javascript:alert(\"$col[0]: $col[1]\")'>$col[0]</a></th>";
    echo "</tr>";

    // Tabelleninhalt
    foreach ( $teams as $mid=>$zahlen ){
        // Mannschaft überhaupt in der Staffel?
        if ( !$zahlen ['topX'] ) continue;
        echo "<tr><td class='l'>&nbsp;".SED_TeamLink ( $mid )."&nbsp;&nbsp;</td>";

        // Zahlen
        foreach ( $table as $col=>$blub ){
            if ( $zahlen [$col] == $teamMax [$col] )
                echo "<td><b>".$zahlen[$col]."</b></td>";
            else
                echo "<td>".$zahlen[$col]."</td>";
        }
        echo "</tr>";
    }

    // Durchschnitt
    echo "<tr><td class='l'>&nbsp;<b>Durchschnitt:</b></td>";
    foreach ( $table as $col=>$blub ){
        echo "<td><b>".round($teamAvg [$col])."</b></td>";
    }
    echo "</tr>";
    echo "</table>";

    ////////////////////////////////////////////////////////
    // Top-Scorer
    ////////////////////////////////////////////////

    echo "<br /><br /><span class='sed_hl2'>Topscorer</span><br /><br />";
    echo "<table cellpadding='3' class='sed_tabelle'><tr><th>Name</th><th>DWZ</th><th>Mannschaft</th><th>Brett</th><th>Partien</th><th>Punkte</th></tr>";

    // Ausgabe
    foreach ( $topscorer as $id ){
        // Das durchschnittliche Brett etwas schöner
        $spielerd = $spieler [$id];
        $brett = round($spielerd['brett']/$spielerd['partien'],1);
        switch ( round ( ($brett-intval($brett)) * 10 ) ) {
            case 0: case 1: case 2:
                $brett = intval($brett); break;
            case 3: case 4: case 5: case 6:
                $brett = intval($brett)."-".(intval($brett)+1); break;
            case 7: case 8: case 9:
                $brett = intval($brett)+1; break;
        }

        // Ausgabe
        if ( $id )
        echo "<tr>
                <td class='l'>&nbsp;<a href='?spieler=$spielerd[id]'>$spielerd[name]</a>&nbsp;&nbsp;</td>
                <td>$spielerd[dwz]</td>
                <td class='l'>&nbsp;".SED_TeamLink ( $spielerd['mannschaft'] )."</td>
                <td>$brett</td>
                <td>$spielerd[partien]</td>
                <td><b>".str_replace ( ".0", "", SED_Ergebnis($spielerd['punkte']) )."</b></td>
            </tr>";
    }
    echo "</table>";

    ////////////////////////////////////////////////////////
    // Spiel-Statistik
    ////////////////////////////////////////////////

    echo "<br /><br /><span class='sed_hl2'>Spiel-Statistik</span><br /><br />";
    $table = array (
        "partien" => array ( "&sum;", "Wie viele Partien hat die Mannschaft bislang gespielt?", 'Z', 'L' ),
        "+" => array ( "+", "Kampflose Siege", 'Z' ),
        "-" => array ( "-", "Kampflose Niederlagen", 'Z' ),
        "1" => array ( "1", "Siege aus den wirklich gespielten Partien", '%', 'L' ),
        "½" => array ( "&frac12;", "Remis", '%' ),
        "0" => array ( "0", "Niederlagen", '%' ),
        "W" => array ( "W", "Score mit Wei&szlig;", 'S', 'L' ),
        "S" => array ( "S", "Score mit Schwarz", 'S' )
    );
    // Tabellenkopf
    echo "<table cellpadding='3' class='sed_tabelle'><tr><th>Mannschaft</th>";
    foreach ( $table as $col ){
        // Ausgabe
        $style = isset ( $col [3] ) ? "style='border-left:solid 2px;'" : "";
        echo "<th $style>&nbsp;<a title='$col[1]' href='javascript:alert(\"$col[0]: $col[1]\")'>$col[0]</a>&nbsp;</th>";
    }
    echo "</tr>";

    // Tabelleninhalt
    foreach ( $teams as $mid=>$zahlen ){
        // Mannschaft überhaupt in der Staffel?
        if ( !$zahlen ['topX'] ) continue;
        echo "<tr><td class='l'>&nbsp;".SED_TeamLink ( $mid )."&nbsp;&nbsp;</td>";

        // Zahlen
        foreach ( $table as $col=>$options ){
            $zahl = $zahlen [$col];

            // Prozent
            if ( $options [2] == '%' ){
                $divisor = $zahlen['1']+$zahlen['0']+$zahlen['½'];
                $zahl = $divisor ? round($zahlen[$col]*100.0/$divisor).'%' : "";
            }

            // Score
            if ( $options [2] == 'S' ){
                $divisor = $zahlen [$col."Anzahl"];
                $zahl = $divisor ? round(100.0*$zahl/$divisor).'%' : "";
            }

            // Ausgabe
            $style = isset ( $options [3] ) ? "style='border-left:solid 2px;'" : "";
            echo "<td $style>&nbsp;$zahl&nbsp;</td>";
        }
        echo "</tr>";
    }
    // Durchschnitt
    echo "<tr><td class='l'>&nbsp;<b>Summe:</b></td>";
    foreach ( $table as $col=>$options ){
        $zahl = $teamSum [$col];

        // Prozent
        if ( $options [2] == '%' ){
            $divisor = $teamSum['1']+$teamSum['0']+$teamSum['½'];
            $zahl = $divisor ? round($zahl*100.0/$divisor).'%' : "";
        }

        // Score
        if ( $options [2] == 'S' ){
            $divisor = $teamSum [$col."Anzahl"];
            $zahl = $divisor ? round($zahl*100.0/$divisor).'%' : "";
        }

        // Ausgabe
        $style = isset ( $options [3] ) ? "style='border-left:solid 2px;'" : "";
        echo "<td $style><b>$zahl</b></td>";
    }
    echo "</tr>";
    echo "</table>";

?>
