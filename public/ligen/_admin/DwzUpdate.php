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

    $globals['adminScript'] === 'DwzUpdate' or die('Invalid invocation');

    // require_once ( "cache.inc.php" );

    // Aktuelle Saison berechnen (Update nur bis Ende April)
    // In 2020 Update bis Ende September (Corona)...
    if ( date ( "n" ) <= 5 )
        $saison = date ( "Y" ) - 1;
    else
        $saison = date ( "Y" );
    echo "Aktuelle Saison ist: $saison<br />";

    // Alle betroffenen Mannschaften finden, die noch ein Spiel ohne Ergebnis haben.
    $teams = SED_Query("
        SELECT DISTINCT m.id, m.zps
        FROM mannschaften m
        INNER JOIN turniere t ON t.id=m.turnier
        WHERE t.startjahr=? AND EXISTS(
            SELECT 1
            FROM paarungen p
            WHERE p.erg1 IS NULL AND p.erg2 IS NULL
             AND (p.mannschaft1=m.id OR p.mannschaft2=m.id))",
        [$saison]
    )->fetchAllAssociative();
    echo "Anzahl betroffener Mannschaften: ".count($teams)."<br />";

    // Spielern ohne ZPS eine ZPS geben
    foreach($teams as $team){
        // Spieler ohne ZPS suchen
        $spieler = SED_Query("SELECT * FROM spieler s WHERE s.mannschaft=? AND (LENGTH(s.zps)<7 OR s.zps IS NULL)", [$team['id']])->fetchAllAssociative();

        // Anzahl der Spieler ausgeben
        if ( $count = count($spieler) ){
            echo "<b>In der Mannschaft $team[id] haben $count Spieler keine ZPS.</b><br />";

            // Fuer jeden Spieler eine ZPS suchen
            foreach ($spieler as $sp){
                if (  SED_Query (
                    "UPDATE spieler s
                        INNER JOIN dwz_spieler d
                        ON d.Spielername LIKE CONCAT(s.nachname,',',s.vorname,'%')
                        AND (d.ZPS=SUBSTR(?,1,5) OR d.ZPS=SUBSTR(?,6,5))
                    SET s.zps=CONCAT(d.ZPS,'-',d.Mgl_Nr)
                    WHERE s.id=?",
                    [$team['zps'], $team['zps'], $sp['id']])->rowCount())
                {
                    echo "$sp[nachname] hat eine ZPS bekommen.<br />";
                } else {
                    echo "<span style='color:red'>$sp[nachname],$sp[vorname] konnte keine ZPS bekommen.</span><br />";
                }
            }
        }
    }

    // DWZ-Update
    $count = 0;
    foreach($teams as $team) {
        $count += SED_Query (
            "UPDATE spieler s
                INNER JOIN dwz_spieler d
                ON d.ZPS=SUBSTR(s.zps,1,5) AND d.Mgl_Nr=SUBSTR(s.zps,7)
            SET s.dwz=d.DWZ, s.elo=d.FIDE_Elo, s.geschlecht=LOWER(d.Geschlecht), s.geburt=d.Geburtsjahr
            WHERE s.mannschaft=? AND (s.dwz IS NULL or s.dwz<>d.DWZ OR s.elo<>d.FIDE_Elo)",
            [$team['id']]
        )->rowCount();
    }
    echo "Habe $count Wertungszahlen aktualisiert.";

    // Cache löschen
    // SED_Cache::clear (); // also für alle Turniere
