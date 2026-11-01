<?php
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

    // Der Cache ist deaktiviert, alle Funktionen sind wirkungslos.
    static function cache ( $data, $typ, $runde = 0, $staffel = 0, $turnier = 0 ){}
    static function load ( $typ, $runde = 0, $staffel = 0 ){ return false; }
    static function clearTeam($mid, $type) {}
    static function clearTables($staffel = 0) {}
    static function clearSpieltag($staffel = 0, $runde = 0) {}
    static function clearAll($staffel = 0) {}
}
?>
