<?
/* Berechnung der Tabelle
 *
 * Dieses Skript berechnet die Tabelle zu gegebener Staffel und Runde.
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage spieltag
 */
  /*
    --- Tabellen-Format (Matrix) ---
    Platz, Mannschafts-ID, Mannschaft, [Ergebnisse] MP, BP, aufsteiger?
    *
    * Hier die Sortierungen von ein paar Organisationen
    * <default> = MP, BP, DV (MP, BP, Berliner), Los
    * NSV       default
    * JBLN      default (2 MP gibt es aber erst ab 3BP... + Einzelsiegwertung)
    * NSJ       default
    * SJBH      default (+Siegwertung)
    * BEZ1      default
    * FRL       MP,BP,Stichkampf
    * BEZ6      default
  */

    require_once ( "turnier.inc.php" );
    require_once ( "cache.inc.php" );
    define ( "SED_SORT_DEFAULT", 1 ); // MP, BP, Direkt ( MP, BP, Berliner ), Los

// Objekte für eine Mannschaft (eine Zeile)
class SED_Tabelle_Team {
    private $id, $mp, $bp;
    private $ergebnisse; // $ergebnisse [gegner][spieltag] = array ( bp, mp )

    // Konstruktor
    function __construct ( $mid ){
        $this->id = $mid;
        $this->mp = 0;
        $this->bp = 0.0;
    }

    // Speichert ein Ergebnis
    function setErgebnis ( $gegner, $spieltag, $mp, $bp ){
        $this->ergebnisse [$gegner][$spieltag] = array ( $bp, $mp );
        $this->mp += $mp;
        $this->bp += $bp;
    }

    // Liefert die Mannschafts-ID
    function getID (){
        return $this->id;
    }

    // Liefert die Mannschaftspunkte
    function getMP (){
        return $this->mp;
    }

    // Liefert die Brettpunkte
    function getBP (){
        return $this->bp;
    }

    // Liefert die Mannschaftspunkte gegen einen bestimmten Gegner
    function getMPvs ( $gegner ){
        $mp = 0;
        foreach ( $this->getErgebnisse ( $gegner ) as $spiel )
            $mp += $spiel [1];
        return $mp;
    }

    // Liefert die Brettpunkte gegen einen bestimmten Gegner
    function getBPvs ( $gegner ){
        $bp = 0.0;
        foreach ( $this->getErgebnisse ( $gegner ) as $spiel )
            $bp += $spiel [0];
        return $bp;
    }

    // Liefert Ergebnisse gegen einen bestimmten Gegner
    function getErgebnisse ( $gegner ){
        // Überhaupt vorhanden?
        if ( !isset ( $this->ergebnisse [$gegner] ) )
            return array ();
        return $this->ergebnisse [$gegner];
    }

    // Liefert Ergebnisse gegen einen Gegner mit Link-Informationen
    function getErgebnisLinks ( $gegner, $staffel ){
        global $globals;
        $return = array ();
        foreach ( $this->getErgebnisse ( $gegner ) as $spieltag=>$bp ){
            $ret["url"] = "?staffel=$staffel&r=".$spieltag."#p".$this->getID()."x".$gegner;
            $ret["title"] = "gegen ".$globals["teams"][$gegner];
            $ret["text"] = SED_Ergebnis ( $bp[0] );
            $return [] = $ret;
        }
        return $return;
    }
}

class SED_Tabelle {
    // Instanzvariablen
    private $staffel;
    private $teams; // mid => Team-Object
    private $ranking; // list of tupel ( platz, mid )

    // Konstrukor
    function __construct ( $staffel, $runde ){
        global $globals;
        global $prefs;
        $this->staffel = $staffel;
        $this->teams = array();

        // Mannschaften holen
        $rows = SED_Query(
          "SELECT m.id FROM mannschaften AS m WHERE m.staffel = ?",
          [$staffel]
        )->fetchAllAssociative();
        foreach ($rows as $team) {
          $this->teams[$team['id']] = new SED_Tabelle_Team($team['id']);
        }

        // Ergebnisse holen
        $games = SED_Query(
          "SELECT mannschaft1 AS mid1, mannschaft2 AS mid2, erg1, erg2, runde 
          FROM paarungen 
          WHERE staffel = ? AND runde <= ? AND erg1 IS NOT NULL AND erg2 IS NOT NULL",
          [$staffel, $runde]
        )->fetchAllAssociative();
        foreach ($games as $game) {        
            // Mannschaften überhaupt in der Tabelle vorhanden?
            if ( !isset ( $this->teams [$game["mid1"]] ) || !isset ( $this->teams [$game["mid2"]] ) )
                continue;

            // Mannschaftspunkte berechnen
            // 1. Gewonnen hat die Mannschaft mit mehr BP (am verbreitetsten)
            // Ausnahme: Ein 0 zu 0 werten wir als 0 MP für beide Parteien
            $mp1 = $mp2 = 0;
            if ( $game["erg1"] > 0 || $game["erg2"] > 0 ){
                $mp1 = $game["erg1"] > $game["erg2"] ? 2 : ( $game["erg1"] < $game["erg2"] ? 0 : 1 );
                $mp2 = 2 - $mp1;
            }

            // 2. wie 1. aber man kann nur gewinnen mit mehr als 3 BP bei 6 Brettern+, 3BP ist 1 MP
            $specialOrgs = array("fbl", "frl", "ndsj", "703", "703j", "A");
            if ($prefs["startjahr"] > 2014) array_push($specialOrgs, "7", "7p");
			if (in_array($prefs["organisation"], $specialOrgs)) {
				$bz = SED_GetBrettzahl($staffel)/2;
				$mp1 = ($game["erg1"]==$bz ? 1 : ($game["erg1"]>$bz ? 2 : 0));
				$mp2 = ($game["erg2"]==$bz ? 1 : ($game["erg2"]>$bz ? 2 : 0));
			}

            // Ergebnisse speichern
            $this->teams [$game["mid1"]]->setErgebnis ( $game["mid2"], $game["runde"], $mp1, $game["erg1"] );
            $this->teams [$game["mid2"]]->setErgebnis ( $game["mid1"], $game["runde"], $mp2, $game["erg2"] );
        }
    }

    // Tabelle sortieren (mittels countingsort)
    function sort ( $type ){
        // Einordnen über sort [mp][bp] = listOfTeams
        $sort = array ();
        foreach ( $this->teams as $mid=>$team ){
            $sort [$team->getMP()][(string) $team->getBP()][] = $team;
        }

        // Direkten Vergleich zwischen Teams durchführen
        foreach ( $sort as &$mpgleich )
            foreach ( $mpgleich as &$bpgleich )
                if ( count ( $bpgleich ) > 1 )
                    $bpgleich = $this->direkterVergleich ( $bpgleich, $type );

        // sortiert ausgeben
        krsort ( $sort );
        $this->ranking = array ();
        $platz = 1;
        foreach ( $sort as $mp => $bpgroups ){
            // Brettpunkte absteigend sortieren
            krsort ( $bpgroups );

            // Jetzt innerhalb der BP gucken
            foreach ( $bpgroups as $bp=>$teams ){
                // Es gibt hier nur eine Mannschaft
                if ( count ( $teams ) == 1 ){
                    $this->ranking [] = array ( $platz++, $teams[0]->getID() );
                    continue;
                }

                // Ansonsten Format von dV: tupel (platz, team)
                foreach ( $teams as $tupel )
                    $this->ranking [] = array ( $platz+$tupel[0], $tupel[1]->getID() );

                // Nächste Platznummer
                $platz += count ( $teams );
            }
        }
    }

    // Sortiert eine Liste von Team-Objekten nach direktem Vergleich
    // RÜckgabe Array mit Tupel ( Platz, Team )
    static function direkterVergleich ( $teams ){
        // Mannschafts- und Brettpunkte und Berliner Wertung untereinander berechnen
        $mp = array (); // mid => mp
        $bp = array (); // mid => bp
        $bw = array (); // mid => bw
        for ( $a = 0; $a < count ( $teams ); ++$a ){ // init
            $mid = $teams[$a]->getID();
            $mp [$mid] = $bp [$mid] = $bw [$mid] = 0;
        }

        // Alle möglichen Paarungen durchgehen
        for ( $a = 0; $a < count ( $teams ); ++$a ){
            $mid1 = $teams [$a]->getID ();

            // Mögliche Gegner
            for ( $b = 0; $b < count ( $teams ); ++$b ){
                // Natürlich nicht gegen sich selbst
                if ( $a == $b ) continue;
                $mid2 = $teams [$b]->getID ();

                // MP
                $mp [$mid1] += $teams [$a]->getMPvs ( $mid2 );
                $mp [$mid2] += $teams [$b]->getMPvs ( $mid1 );

                // BP
                $bp [$mid1] += $teams [$a]->getBPvs ( $mid2 );
                $bp [$mid2] += $teams [$b]->getBPvs ( $mid1 );

                // Berliner Wertung
                // Überlegung: Man kann die ruhig hier schon berechnen, denn wenn die Mannschaften schon gegeneinander gespielt haben, ist es durchaus wahrscheinlich, dass die BW benötigt wird. Außerdem wird die Tabelle ja normalerweise gecacht.
                if ( count ( $teams [$a]->getErgebnisse ( $mid2 ) ) ){
                    $berliner = SED_Tabelle::berlinerWertung ( $mid1, $mid2 );
                    $bw [$mid1] += $berliner [0];
                    $bw [$mid2] += $berliner [1];
                }
            }
        }

        // Jetzt mittels Countingsort sortieren
        $sort = array ();
        for ( $a = 0; $a < count ( $teams ); ++$a ){
            $id = $teams[$a]->getID();
            $sort [$mp[$id]][(string) $bp[$id]][(string) $bw[$id]][] = $teams [$a];
        }

        // sortiert zurückgeben
        $sorted = array ();
        $platz = 0;
        krsort ( $sort );
        foreach ( $sort as $mp => $bpgroups ){
            // Jetzt innerhalb der BP gucken
            krsort ( $bpgroups );
            foreach ( $bpgroups as $bp=>$bwgroups ){
                // Jetzt innerhalb der BW gucken
                krsort ( $bwgroups );
                foreach ( $bwgroups as $bw=>$teams ){
                    // Speichern mit platz => team
                    foreach ( $teams as $team )
                        $sorted [] = array ( $platz, $team );

                    // Nächste Platznummer
                    $platz += count ( $teams );
                }
            }
        }
/*
foreach ( $sorted as $team )
    echo "$team[0] ".$team[1]->getID()."<br>";
foreach ( $sort as $mp=>$a )
    foreach ( $a as $bp=>$b )
        foreach ( $b as $bw=>$c )
            foreach ( $c as $team )
                echo "$mp $bp $bw ".$team->getID()."<br>";
*/
        return $sorted;
    }

    // Berliner Wertung
    static function berlinerWertung ( $m1, $m2 ){
        $bw = array ( 0.0, 0.0 );

        // Einzelergebnisse laden
        $rows = SED_Query(
          "SELECT sp.ergebnis1, sp.ergebnis2, sp.brett 
          FROM spielerpaarungen sp 
          INNER JOIN paarungen p ON p.id = sp.paarung 
          WHERE p.mannschaft1 = ? AND p.mannschaft2 = ?",
          [$m1, $m2]
        )->fetchAllAssociative();
        $brettzahl = count($rows);

        foreach ($rows as $spiel) {
          $bw[0] += ($brettzahl - $spiel["brett"] + 1) * SED_Tabelle::valueOfSpielergebnis($spiel["ergebnis1"]);
          $bw[1] += ($brettzahl - $spiel["brett"] + 1) * SED_Tabelle::valueOfSpielergebnis($spiel["ergebnis2"]);
        }

        return $bw;
    }

    // Tabellen-Array berechnen
    function getTable ( $kreuztabelle ){
        // Keine Mannschaft?
        if ( count ( $this->teams ) == 0 ) return array ();
        global $globals;

        // Tabellenkopf anlegen
        $table = array ( array ( "", "Mannschaft" ) );
        if ( $kreuztabelle )
            for ( $r = 1; $r <= count ( $this->teams ); ++$r )
                $table [0][] = $r;
        $table [0][] = "MP";
        $table [0][] = "BP";

        // Alle Platzierungen durchgehen (tupel (platz, mid))
        foreach ( $this->ranking as $tupel ){
            $mid = $tupel [1];
            $team = $this->teams [$mid];

            // Rang und Mannschaft
            $row = array ( $tupel[0]."." );
            $row [] = array ( "text"=>$globals ['teams'][$mid], "url"=>"?mannschaft=$mid", "title"=>"Zur Mannschaftsaufstellung" );

            // Ergebnisse
            if ( $kreuztabelle )
            for ( $gegner = 0; $gegner < count ( $this->ranking ); ++$gegner ){
                $mid2 = $this->ranking [$gegner][1];

                if ( $mid2 == $mid )
                    // selbst?
                    $row [] = "xxx";
                else
                    // Ergebnis schreiben
                    $row [] = $team->getErgebnisLinks ( $mid2, $this->staffel );
            }

            // Felder MP und BP
            $row [] = $team->getMP ();
            $row [] = SED_Ergebnis ( $team->getBP () );
            $row [] = ""; // für Aufsteiger/Absteiger
            $table [] = $row;
        }
        return $table;
    }

    // Wert eines Einzelergebnisses
    static function valueOfSpielergebnis ( $erg ){
        switch ( $erg ){
            case "+":
            case "1":
                return 1;
            case SED_REMIS:
                return 0.5;
            default:
                return 0;
        }
    }
}

// Gibt eine sortierte Kreuz-Tabelle eines bestimmten Spieltages einer bestimmten Staffel zurück
function Tabelle ( $staffel, $runde, $kreuztabelle, $sortmode = SED_SORT_DEFAULT )
{
    global $globals;
    global $prefs;

    // Simple Parameter Überprüfung
    if ( isset ( $globals ['staffeln'][$staffel] ) == false || is_numeric ( $runde ) == false )
        return array ();

    // Typ berechnen
    $typ = $kreuztabelle ? "Kreuztabelle" : "Tabelle";

    // Hack für Corona-Saison: Immer die Tabelle mit Ergebnissen aller Runden zurückgeben.
    if ( $prefs['startjahr'] == '2021' ) {
      $runde = $prefs['runden'];
    }
  
    // Im Cache?
    if ( $data = SED_Cache::load ( $typ, $runde, $staffel ) )
        return $data;

    // Ansonsten neu generieren
    $table = new SED_Tabelle ( $staffel, $runde );
    $table->sort ( $sortmode );
    $data = $table->getTable ( $kreuztabelle );

    // Infos zur Staffel (wie Aufsteiger-Anzahl)
    $infos = mysql_fetch_array ( mysql_query ( "SELECT * FROM viewStaffeln WHERE id=$staffel", $globals ['db'] ), MYSQL_ASSOC );
    if ( !is_array ( $infos ) || !$infos ['showTabelle'] )
        return array ();

    // Aufsteiger und Absteiger
    if ( count ( $data ) ){
        $i = 0;
        for ( $i = 1; $i <= $infos ['spielAufsteiger'] && isset($data[$i]); ++$i )
            $data [$i][count($data[$i])-1] = "aufsteiger";
        for ( ; $i <= $infos ['spielAufsteiger']+$infos ['spielAufsteigerRelegation'] && isset($data[$i]); ++$i )
            $data [$i][count($data[$i])-1] = "aufsteigerRelegation";
        for ( $i = count($data)-1; $i >= count($data)-$infos ['spielAbsteiger'] && isset($data[$i]); --$i )
            $data [$i][count($data[$i])-1] = "absteiger";
        for ( ; $i >= count($data)-$infos ['spielAbsteiger']-$infos ['spielAbsteigerRelegation'] && isset($data[$i]); --$i )
            $data [$i][count($data[$i])-1] = "absteigerRelegation";
    }

    // Im Cache speichern
    SED_Cache::cache ( $data, $typ, $runde, $staffel, $globals ["tid"] );
    return $data;
}
?>
