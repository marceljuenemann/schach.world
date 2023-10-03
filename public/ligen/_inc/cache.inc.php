<?
/* Cache Bibliothek
 * 
 * In dieser Datei wird die Klasse SED_Cache zur Verfügung
 * gestellt, mit der ein PHP-Array in die Datenbank geschrieben werden
 * kann. Es kann auch geprüft werden, ob ein gewünschter Eintrag in
 * der Datenbank existiert und es können auch Einträge gelöscht werden,
 * wenn sie nicht länger gültig sind.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

class SED_Cache {
    const TEAM_AUFSTELLUNG = "TeamAufstellung";
    const TEAM_SPIELPLAN = "TeamSpielplan";
    const TEAM_ERGEBNISSE = "TeamErgebnisse";
    const TABELLE = "Tabelle";
    const KREUZTABELLE = "Kreuztabelle";
    const SPIELTAG = "Spieltag";

    // Speichert etwas im Cache
    static function cache ( $data, $typ, $runde = 0, $staffel = 0, $turnier = 0 ){ 
        global $globals;

        // Turnier selber berechnen
        if ( !$turnier ) $turnier = $globals ["tid"];
 
        // In den Cache speichern
        mysql_query ( $x= "REPLACE INTO cache SET turnier='$turnier', staffel='$staffel', runde='$runde', typ='$typ', inhalt='". str_replace ( "'", "\\'", serialize ( $data ) ) ."'", $globals ['db'] );
    }

    // Lädt etwas aus dem Cache
    static function load ( $typ, $runde = 0, $staffel = 0 ){
        global $globals;
        
        // Aus dem Cache laden 
        $rsrc = mysql_query ( "SELECT inhalt FROM cache WHERE typ='$typ' AND staffel='$staffel' AND runde='$runde' LIMIT 1", $globals ['db'] );
        if ( $rsrc && mysql_num_rows ( $rsrc ) && !isset ( $_GET ['nocache'] ) ){
            $row = mysql_fetch_array ( $rsrc, MYSQL_NUM );
            return unserialize ( reset ( $row ) );
        }	
        return false;
    }
        
    // Löscht etwas aus dem Cache
    static function clear ( $options = "1" ){
        global $globals;
        return mysql_query ( "DELETE FROM cache WHERE $options", $globals ['db'] );
    }

    // Löscht eine Mannschaft
    static function clearTeam ( $mid, $type ){
        global $globals;
        $sql = "turnier='$globals[tid]' AND typ='$type'";
        $sql .= $mid ? " AND runde='$mid'" : "";
        return SED_Cache::clear ( $sql );
    }

    // Löscht die Tabellen
    static function clearTables ( $staffel = 0 ){
        global $globals;
        $sql = "turnier='$globals[tid]' AND (typ='Tabelle' OR typ='Kreuztabelle')";
        $sql .= $staffel ? " AND staffel='$staffel'" : "";
        return SED_Cache::clear ( $sql );
    }
    
    // Löscht eine Spieltag-Ansicht
    static function clearSpieltag ( $staffel = 0, $runde = 0 ){
        global $globals;
        $sql = "turnier='$globals[tid]' AND (typ='Spieltag' OR typ='MatchDay')";
        $sql .= $staffel ? " AND staffel='$staffel'" : "";
        $sql .= $runde ? " AND runde='$runde'" : "";
        return SED_Cache::clear ( $sql );
    }

    // Löscht alles
    static function clearAll ( $staffel = 0 ){
        global $globals;
        $sql = "turnier='$globals[tid]'";
        $sql .= $staffel ? " AND staffel='$staffel'" : "";
        return SED_Cache::clear ( $sql );
    }
}
?>
