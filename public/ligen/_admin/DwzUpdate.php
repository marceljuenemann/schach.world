<?
/* DWZ Update
 *
 * Versucht zu Spielern ohne ZPS-Angabe an Hand des Namens solch eine
 * zu finden und aktualisiert die DW-Zahlen bei Turnieren der aktuellen
 * Saison.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage admin
 */

    require_once ( "admin.inc.php" );
    require_once ( "cache.inc.php" );
    if ( $_GET ["auth"] != substr ( $globals ["masterpasswort"], 5, 5 ) )
        SED_Error ( "Keine Berechtigung!", true );

    // Aktuelle Saison berechnen (Update nur bis Ende April)
    // In 2020 Update bis Ende September (Corona)...
    if ( date ( "n" ) <= 4 || (date ("Y") == '2020' && date ( "n" ) <= 10) )
        $saison = date ( "Y" ) - 1;
    else
        $saison = date ( "Y" );
    echo "Aktuelle Saison ist: $saison<br />";

    // Alle betroffenen Mannschaften finden, die noch ein Spiel ohne Ergebnis haben.
    // $teams = mysql_query ( "SELECT m.id, m.zps FROM mannschaften m INNER JOIN turniere t ON t.id=m.turnier WHERE t.startjahr='$saison'", $globals ["db"] );
    $teams = mysql_query ( "SELECT DISTINCT m.id, m.zps FROM mannschaften m INNER JOIN turniere t ON t.id=m.turnier WHERE t.startjahr='$saison' AND EXISTS(SELECT 1 FROM paarungen p WHERE p.erg1 IS NULL AND p.erg2 IS NULL AND (p.mannschaft1=m.id OR p.mannschaft2=m.id))", $globals ["db"] );
    echo "Anzahl betroffener Mannschaften: ".mysql_num_rows($teams)."<br />";

    // Spielern ohne ZPS eine ZPS geben
    while ( $team = mysql_fetch_array ( $teams ) ){
        // Spieler ohne ZPS suchen
        $spieler = mysql_query ( "SELECT * FROM spieler s WHERE s.mannschaft=$team[id] AND (LENGTH(s.zps)<7 OR s.zps IS NULL)", $globals ['db'] );

        // Anzahl der Spieler ausgeben
        if ( $spieler && $count = mysql_affected_rows ( $globals ['db'] ) ){
            echo "<b>In der Mannschaft $team[id] haben $count Spieler keine ZPS.</b><br />";

            // Fuer jeden Spieler eine ZPS suchen
            while ( $sp = mysql_fetch_array ( $spieler ) ){
                if (  mysql_query (
                    "UPDATE spieler s
                        INNER JOIN dwz_spieler d
                        ON d.Spielername LIKE CONCAT(s.nachname,',',s.vorname,'%')
                        AND (d.ZPS=SUBSTR('$team[zps]',1,5) OR d.ZPS=SUBSTR('$team[zps]',6,5))
                    SET s.zps=CONCAT(d.ZPS,'-',d.Mgl_Nr)
                    WHERE s.id=$sp[id]"
                    , $globals ['db'] ) && mysql_affected_rows () )
                {
                    echo "$sp[nachname] hat eine ZPS bekommen.<br />";
                } else {
                    echo "<span style='color:red'>$sp[nachname],$sp[vorname] konnte keine ZPS bekommen.</span><br />";
                }
            }
        }
    }

    // DWZ-Update
    mysql_data_seek ( $teams, 0 );
    $count = 0;
    while ( $team = mysql_fetch_array ( $teams ) ){
        $spieler = mysql_query ( $x=
            "UPDATE spieler s
                INNER JOIN dwz_spieler d
                ON d.ZPS=SUBSTR(s.zps,1,5) AND d.Mgl_Nr=SUBSTR(s.zps,7)
            SET s.dwz=d.DWZ, s.elo=d.FIDE_Elo, s.geschlecht=LOWER(d.Geschlecht), s.geburt=d.Geburtsjahr
            WHERE s.mannschaft=$team[id] AND (s.dwz IS NULL or s.dwz<>d.DWZ OR s.elo<>d.FIDE_Elo)"
            , $globals ['db'] );
        $count += mysql_affected_rows ( $globals ['db'] );
    }
    echo "<b>Habe $count Wertungszahlen aktualisiert.</b>";

    // Cache löschen
    SED_Cache::clear (); // also für alle Turniere
?>
