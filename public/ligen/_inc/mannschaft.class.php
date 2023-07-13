<?
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
    getSpielplan
        id
            id
            staffel
            spieltag
            datum
            heim
            mannschaft1
            mannschaft2
            gegner
            gegnername
            mannschaft1name
            mannschaft2name
            erg1
            erg2
            ergebnis
    getAufstellung
        id
            * from spieler
            berechtigtAb
            mannschaft
            istErsatz
            zpsverein
            istGastspieler
    getErgebnisse
        id
            1
                0 (1. Gegner)
                    ergebnis
                    gegner
                    * from spieler (gegner)
                1
                   ...
            2
            ...
            spiele
            pkt
            prozent
            einsaetze
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

		function getZusatzfeldBoolean ($feldname) {
				global $globals;
				$query = "SELECT 1 FROM anmeldungZusatzfelder WHERE feldname LIKE '$feldname' AND mannschaft='".$this->get("id")."' AND LOWER(inhalt)='ja'";
				$rsrc = mysql_query($query, $globals['db']);
				return mysql_num_rows($rsrc) > 0;
		}

    // Allgemeine Daten aus der Tabelle mannschaften laden
    private function loadInfos (){
        global $globals;
        $this->infos = mysql_fetch_array ( mysql_query ( "SELECT * FROM mannschaften as m WHERE m.id=".$this->infos["id"], $globals ['db'] ), MYSQL_ASSOC );
    }

    // Liefert den Spielplan
    function getSpielplan (){
        // im Cache?
        if ( $data = SED_Cache::load ( SED_Cache::TEAM_SPIELPLAN, $this->get("id") ) )
            return $data;

        // Datenbankabfrage
        global $globals;
        $spielplan = array ();
        $ergebnisse = mysql_query (
            "SELECT p.id, p.runde as spieltag, p.staffel, DATE_FORMAT(t.termin,'%d.%m.') as datum, IF(p.mannschaft1=".$this->get("id").",'Heim','Gast') as heim, p.mannschaft1, p.mannschaft2, p.erg1, p.erg2, IF(p.mannschaft1=".$this->get("id").",p.mannschaft2,p.mannschaft1) gegner
            FROM paarungen p
            INNER JOIN viewTermine t ON t.paarung=p.id
            WHERE p.mannschaft1=".$this->get("id")." OR p.mannschaft2=".$this->get("id")." AND p.staffel>0
            ORDER BY p.runde", $globals ['db'] );

        // Anfrage verarbeiten
        while ( $spiel = mysql_fetch_array ( $ergebnisse, MYSQL_ASSOC ) )
        {
            // Setzen des Gegnernamens
            $gegner = new SED_Mannschaft ( $spiel ["gegner"] );
            $spiel ['gegnername'] = $gegner->get("mannschaftsname");

            // Setzen der Mannschaftsnamen
            if ( $spiel ['heim'] == "Heim" ){
                $spiel ['mannschaft1name'] = $this->get("mannschaftsname");
                $spiel ['mannschaft2name'] = $spiel ['gegnername'];
            } else {
                $spiel ['mannschaft2name'] = $this->get("mannschaftsname");
                $spiel ['mannschaft1name'] = $spiel ['gegnername'];
            }

            // Setzen des Ergebnisses aus eigener Sicht
            $erg1 = SED_Ergebnis ( $spiel ['erg1'] );
            $erg2 = SED_Ergebnis ( $spiel ['erg2'] );
            $spiel ['ergebnis'] = ( $spiel ['heim'] == "Heim" ? "$erg1:$erg2" : "$erg2:$erg1" );

            // Spielverlegung auf unbekannten Termin behandeln
            if ( $spiel ['datum'] == "24.12." )
                $spiel ['datum'] = "??.??.";

            // Speichern
            $spielplan [$spiel ["id"]] = $spiel;
        }

        // cachen
        SED_Cache::cache ( $spielplan, SED_Cache::TEAM_SPIELPLAN, $this->get("id"), $this->get("staffel") );
        return $spielplan;
    }

    // Liefert die Aufstellung
    function getAufstellung (){
        // im Cache?
        if ( $data = SED_Cache::load ( SED_Cache::TEAM_AUFSTELLUNG, $this->get("id") ) )
            return $data;

        // Vorbereitungen
        global $globals; global $prefs;
        $aufstellung = array ();
        if ( !isset ( $prefs ['spielErsatzmannschaft'] ) )
            $prefs ['spielErsatzmannschaft'] = 0;

        // Aufstellung abfragen
        $rsrc = mysql_query (
            "SELECT s.*,
                IF(s.nmR IS NULL,0,s.nmR) berechtigtAb,
                s.mannschaft, s.mannschaft<>".$this->get("id")." as istErsatz
            FROM mannschaften m
            INNER JOIN mannschaften m2 ON
                m.turnier=m2.turnier AND
                (m.id=m2.id OR m.mnr<m2.mnr) AND
                m.mnr+$prefs[spielErsatzmannschaft]>=m2.mnr AND
                IF(m.zps IS NULL, m.name=m2.name, m.zps=m2.zps) AND
                m.gruppe=m2.gruppe
            INNER JOIN spieler s ON s.mannschaft=m2.id
            WHERE m.id=".$this->get("id")."
            ORDER BY m2.mnr, s.brettnr", $globals ['db'] );

        // Verarbeiten
        while ( $spieler = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ){
            // Gastspieler?
            $spieler ['zpsverein'] = substr ( $spieler ["zps"], 0, 5 );
            $spieler ['istGastspieler'] = (bool) (
                strpos ( $this->get("zps"), $spieler ["zpsverein"] ) === false
                && $this->get ( "zps" ) && $spieler ["zpsverein"] );
            $aufstellung [] = $spieler;
        }

        // cachen
        SED_Cache::cache ( $aufstellung, SED_Cache::TEAM_AUFSTELLUNG, $this->get("id"), $this->get("staffel") );
        return $aufstellung;
    }

    // Liefert die Einzelergebnisse
    function getErgebnisse (){
        // im Cache?
        if ( $data = SED_Cache::load ( SED_Cache::TEAM_ERGEBNISSE, $this->get("id") ) )
            return $data;

        // Vorbereitungen
        global $globals;
        $ergebnisse = array ();

        // Alle Einzelergebnisse abfragen
        $rsrc = mysql_query (
            "SELECT IF(p.mannschaft1=m.id, sp.spieler1, sp.spieler2) spieler, p.runde, IF(p.mannschaft1=m.id, sp.ergebnis1, sp.ergebnis2) as ergebnis, g.*
            FROM mannschaften m
            INNER JOIN paarungen p ON p.mannschaft1=m.id OR p.mannschaft2=m.id
            INNER JOIN spielerpaarungen sp ON sp.paarung=p.id
            LEFT JOIN spieler g ON g.id=IF(p.mannschaft1=m.id,sp.spieler2,sp.spieler1)
            WHERE m.id=".$this->get("id"), $globals ['db'] );

        // Ergebnisse im Array ergebnisse speichern und Statistik führen
        while ( $partie = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) )
        {
            // Name des Gegners
            $partie ["gegner"] = SED_Spielername ( $partie );

            // Speichern
            if ( !isset ( $ergebnisse [$partie ['spieler']][$partie ['runde']] ) )
                $ergebnisse [$partie ['spieler']][$partie ['runde']] = array ();
            $ergebnisse [$partie ['spieler']][$partie ['runde']][] = $partie;

            // Existenz der Statistikfelder sicherstellen
            if ( !isset ( $ergebnisse [$partie ['spieler']]["pkt"] ) )
            {
                $ergebnisse [$partie ['spieler']]["pkt"] = 0;
                $ergebnisse [$partie ['spieler']]["spiele"] = 0;
                $ergebnisse [$partie ['spieler']]["einsaetze"] = 0;
            }

            // Statistik führen
            switch ( $partie ['ergebnis'] ){
                case "1":
                    $ergebnisse [$partie ['spieler']]["pkt"] += 0.5;
                case SED_REMIS:
                    $ergebnisse [$partie ['spieler']]["pkt"] += 0.5;
                case "0":
                    $ergebnisse [$partie ['spieler']]["spiele"] += 1;
                    break;
                default:
                    break;
            }
            $ergebnisse [$partie ['spieler']]["einsaetze"] += 1;

            // Prozentsatz berechnen
            $ergebnisse [$partie ['spieler']]["prozent"] =
                $ergebnisse [$partie ['spieler']]["spiele"]
                ? round ( $ergebnisse [$partie ['spieler']]["pkt"] / $ergebnisse [$partie ['spieler']]["spiele"] * 100 )
                : "";
        }

        // Spieler finden, die in einer höheren Mannschaft gespielt haben
        $rsrc = mysql_query (
            "SELECT m.id as ersatz, p.staffel, p.runde, s.id spieler, IF(p.mannschaft1=m.id,sp.ergebnis1,sp.ergebnis2) ergebnis, IF(p.mannschaft1=m.id,p.mannschaft2,p.mannschaft1) gegner
            FROM mannschaften m
            INNER JOIN paarungen p ON p.mannschaft1=m.id OR p.mannschaft2=m.id
            INNER JOIN spielerpaarungen sp ON sp.paarung=p.id
            INNER JOIN spieler s ON s.mannschaft=".$this->get("id")." AND s.id=IF(p.mannschaft1=m.id,sp.spieler1,sp.spieler2)
            WHERE m.turnier='$globals[tid]' AND m.zps='".$this->get("zps")."' AND m.mnr<".$this->get("mnr"), $globals ["db"] );
        while ( $ersatz = mysql_fetch_array ( $rsrc, MYSQL_ASSOC ) ){
            if (!is_array($ergebnisse [$ersatz["spieler"]] [$ersatz["runde"]]))
                $ergebnisse [$ersatz["spieler"]] [$ersatz["runde"]] [] = $ersatz;
        }

        // cachen
        SED_Cache::cache ( $ergebnisse, SED_Cache::TEAM_ERGEBNISSE, $this->get("id"), $this->get("staffel") );
        return $ergebnisse;
    }
}
?>
