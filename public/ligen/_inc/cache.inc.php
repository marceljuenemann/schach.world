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

    // Speichert etwas im Cache
    static function cache ( $data, $typ, $runde = 0, $staffel = 0, $turnier = 0 ){ 
        global $globals;

        // Turnier selber berechnen
        if ( !$turnier ) $turnier = $globals ["tid"];
 
        // In den Cache speichern
        SED_Query ( 'REPLACE INTO cache SET turnier=?, staffel=?, runde=?, typ=?, inhalt=?', [$turnier, $staffel, $runde, $typ, serialize ( $data )]);
    }

    // Lädt etwas aus dem Cache
    static function load ( $typ, $runde = 0, $staffel = 0 ){
        // Aus dem Cache laden 
        if (!isset($_GET['nocache'])) {
          $result = SED_Query(
              "SELECT inhalt FROM cache WHERE typ = ? AND staffel = ? AND runde = ? LIMIT 1",
              [$typ, $staffel, $runde]
          )->fetchOne();
          if ($result !== false) {
            return unserialize($result);
          }
        }
        return false;
    }

    // Löscht eine Mannschaft
    static function clearTeam($mid, $type) {
      return self::clearAll();
    }

    // Löscht die Tabellen
    static function clearTables($staffel = 0) {
      return self::clearAll();
    }

    // Löscht eine Spieltag-Ansicht
    static function clearSpieltag($staffel = 0, $runde = 0) {
      return self::clearAll();
    }

    // Löscht alles
    static function clearAll($staffel = 0) {
      global $globals;
      return SED_Query('DELETE FROM cache WHERE turnier = ?', [$globals['tid']]);
    }
}
