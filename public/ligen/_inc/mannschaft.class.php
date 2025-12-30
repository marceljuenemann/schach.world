<?php
/* Abfrage von Mannschaftsdaten
 *
 * In dieser Datei wird die Klasse SED_Mannschaft zur Verfügung
 * gestellt, mit der verschiedene Informationen über eine Mannschaft
 * abgefragt werden können: Allgemeine Infos, Spiellokal, Mannschafts-
 * führer, Spielplan, Aufstellung, Einzelergebnisse.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage mannschaft
 */

  /*
  --- Struktur ---
    get
        * aus mannschaften
        mannschaftsname
    getAufstellung
        id
            * from spieler
            berechtigtAb
            mannschaft
            istErsatz
            zpsverein
            istGastspieler
  */
// does not require turnier!
require_once ( "cache.inc.php" );

class SED_Mannschaft {
    private $infos;

    // Konstruktor: Lade Mannschaft
    function __construct ( $id ){
        if ( !is_numeric ( $id ) || !$id )
            SED_Error ( "MID muss numerisch sein!", true );
        $this->infos ["id"] = $id;
    }

    // Liefert eine Info aus der Datenbank
    function get ( $feld ){
        // Anzuzeigender Mannschaftsname (ggf. ohne 1 am Ende)
        if ( $feld == "mannschaftsname" ){
            global $globals;
            if ( isset ( $globals ['teams'][$this->get("id")] ) )
                // Name wurde schon berechnet (meistens der Fall)
                return $globals ['teams'][$this->get("id")];
            else {
                // Mannschaftsnamen zusammensetzen
                $result = $this->get ( "name" );								
                // das ist so ein dreckiger hack...	
                $result = str_replace ( "BadSalzdetfurth", "Bad Salzdetfurth", $result );
                if ( ( $mnr = $this->get ( "mnr" ) ) > 1 )
                    $result .= " $mnr";
                return $result;
            }
        }

        // Felder aus mannschaften
        if ( !isset ( $this->infos [$feld] ) )
            $this->loadInfos ();
        return $this->infos [$feld];
    }

    // Allgemeine Daten aus der Tabelle mannschaften laden
    private function loadInfos (){
        $this->infos = SED_Row('SELECT * FROM mannschaften as m WHERE m.id=?', [$this->infos["id"]]);
    }

    // Liefert die Aufstellung
    function getAufstellung (){
        // im Cache?
        if ( $data = SED_Cache::load ( SED_Cache::TEAM_AUFSTELLUNG, $this->get("id") ) )
            return $data;

        // Vorbereitungen
        global $prefs;
        $aufstellung = array ();
        if ( !isset ( $prefs ['spielErsatzmannschaft'] ) )
            $prefs ['spielErsatzmannschaft'] = 0;

        // Aufstellung abfragen
        $players = SED_Query(
            "SELECT s.*,
                IF(s.nmR IS NULL,0,s.nmR) berechtigtAb,
                s.mannschaft, s.mannschaft<>? as istErsatz
            FROM mannschaften m
            INNER JOIN mannschaften m2 ON
                m.turnier=m2.turnier AND
                (m.id=m2.id OR m.mnr<m2.mnr) AND
                m.mnr+?>=m2.mnr AND
                IF(m.zps IS NULL, m.name=m2.name, m.zps=m2.zps) AND
                m.gruppe=m2.gruppe
            INNER JOIN spieler s ON s.mannschaft=m2.id
            WHERE m.id=?
            ORDER BY m2.mnr, s.brettnr",
            [$this->get("id"), $prefs['spielErsatzmannschaft'], $this->get("id")]
        )->fetchAllAssociative();

        // Verarbeiten
        foreach ( $players as $spieler ){
            // Gastspieler?
            $spieler ['zpsverein'] = substr ( $spieler ["zps"], 0, 5 );
            $spieler ['istGastspieler'] = (bool) (
                strpos ( $this->get("zps") ?: '', $spieler ["zpsverein"] ) === false
                && $this->get ( "zps" ) && $spieler ["zpsverein"] );
            $aufstellung [] = $spieler;
        }

        // cachen
        SED_Cache::cache ( $aufstellung, SED_Cache::TEAM_AUFSTELLUNG, $this->get("id"), $this->get("staffel") );
        return $aufstellung;
    }
}
?>
