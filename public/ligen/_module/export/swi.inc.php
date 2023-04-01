<? /* charset:utf8 2022-10-10 */
/**
 * Hi Marcel,

das einfachste ist, ein Export im Stil der SwissChess Ausgabe zu
erstellen. Anbei mal ein Beispiel.

Die ersten zwei Zeilen kannst Du ignorieren.

In Zeile 3 ist einkodiert, um was es sich handelt (Turnierart, usw.)
Kannst Du auch ignorieren (davon wird nur die Turnierart ausgelesen,
mehr nicht; für _M_annschafts_R_undenturniere: MR)

Zeile 4: die wichtigste; bitte direkt so übernehmen mit allen Leerzeichen.
Anhand dieser Zeile wird erkannt, dass es sich um eine SwissChess SWI
Datei handelt.

Diese Zeile gibt auch vor, wo was zu stehen hat und wie viele Zeichen
maximal drin stehen dürfen:

Spalten mit "t" : Teilnehmernummer
Spalten mit "r" : wird ignoriert
Spalten mit "n" : Name, Vorname
Spalten mit "v" : Vereins- bzw. Mannschaftsname (wird vorerst noch ignoriert)
Spalten mit "l" : Nation (wird ignoriert)
Spalten mit "f" : die FIDE-ID, hilft bei der Identifizierung des Spielers
Spalten mit "p" : ID des Spielers; ist mittlerweile auch in den
                  Hintergrunddateien
Spalten mit "g" : Geburtsdatum
                  4-stellig: nur das Geburtsjahr
                  6-stellig: DDMMJJ (verk. Geburtsjahr)
                  8-stellig: DDMMJJJJ
Spalten mit "e" : Elo
Spalten mit "d" : DWZ; Elo und DWZ werden nur gebraucht, wenn der
                  Spieler nicht erfasst ist oder keine Zahl vorliegt
Spalten mit "z" : die VKZ des Vereins
Spalten mit "m" : die Mitgliedsnummer in dem Verein

Danach kommen die Ergebnisse,
- wie ("1","0","R","+","-")
- mit welcher Farbe "W","B",":")
- gegen wen (siehe Spalte "t") gespielt wurde.

Pro Runde wird ein solcher Block angehängt; hat einer eine Runde
nicht mitgemacht, sind entsprechend Leerzeichen einzufügen, damit
dann die nächste Runde kommen kann.

Die Datei muss als CP850 kodiert werden, IS0-8859-1(5) oder
UTF-8 geht nicht, sonst sind die Umlaute kaputt und der
Spieler kann nicht identifiziert werden.

Der Name des Turniers wird aus der Zeile mit "Name:" ausgelesen,
das Ende des Turniers aus der mit "Datum(E):"

Wichtig ist, dass die Zeilen mit den Ergebnissen mit einem Leerzeichen
beginnen müssen, alle anderen aber nicht.

Wenn Du sonst Fragen hast, melde Dich einfach.

viele Grüße,
Holger

                                                                     
                                                                     
                                                                     
                                             
6. Stadtmeisterschaft LE 2011

ES  12  7  1 #860#
 ttt. rrr nnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv lll ffffffffff pppppppppp gggggggg eeee dddd  zzzzz mmmm  
   1.   1 Reck, Moritz                     Bebenhausen                                                              2096              1W  6 RB  4 1W 12 1B  2 0W  3 1B 11 1W  5 
   2.   3 Zoellmer, Fritz                  SC Stetten                                                               1958              1B  7 1W 11 RB  3 0W  1 1B  9 RW  4 1B  8 
   3.   2 Schieweck, Gert                  SC Stetten                                                               1875              1W  8 1B 12 RW  2 RB  4 1B  1 RW  5 1B  7 
   4.   5 Gehringer, Frank                 SC Leinfelden                                                            1860              1B  9 RW  1 1B  7 RW  3 0B  5 RB  2 -: 10 
   5.   4 Böhmler, Thomas                  Renningen                                                                1830              0W 11 RB  8 1W 10 1B  6 1W  4 RB  3 0B  1 
   6.  10 Viehoff, Jürgen                  SC Stetten                                                               1772              0B  1 1W  9 0B 11 0W  5 1B 10 0W  7 1B 12 
   7.   8 Pudmensky, Stefan                Oeffingen                                                                1671              0W  2 1B 10 0W  4 1B 11 0W  8 1B  6 0W  3 
   8.   9 Tölg, Wolfgang                   Oeffingen                                                                1601              0B  3 RW  5 0B  9 RW 10 1B  7 1W 12 0W  2 
   9.   7 Kunzi, Jürgen                    SG Filder                                                                1580              0W  4 0B  6 1W  8 1B 12 0W  2 1B 10 RW 11 
  10.  11 Menzel, Siegfried                SC Leinfelden                                                            1481  C0512  135  0W 12 0W  7 0B  5 RB  8 0W  6 0W  9 +:  4 
  11.   6 Schmidt, Heiko                   SC Stetten                                                               1462  C0520   80  1B  5 0B  2 1W  6 0W  7 1B 12 0W  1 RB  9 
  12.  12 Charalambakis, Michail           SV Altbach                                                               1138              1B 10 0W  3 0B  1 0W  9 0W 11 0B  8 0W  6 
###
Name:       6. Stadtmeisterschaft LE 2011           
Ort:        Leinfelden-Echterdingen                 
FIDE-Land:                                          
Datum(S):   13.10.2011           Datum(E):   16.02.2012          
Züge(1):    90 Min.              Züge(2):                         Züge(3):                        
Hauptschiedsrichter:Jürgen Viehoff                                              
Weitere Schiedsrichter:Jürgen Viehoff                                              
Anwender:        Schachkreis-Stuttgart-West                                  
Ser.Nummer:      9603051462                                                  

*/

class SWI_Mannschaft {
	private $data;
	
	function __construct($id){
		$r = mysql_query("SELECT name, mnr, Vereinname as vname FROM mannschaften m LEFT JOIN dwz_vereine d ON d.ZPS=m.zps WHERE m.id='$id'");
		if (mysql_num_rows($r) != 1){
			die("SWI_Mannschaft failed to construct for id: ".$id);
		}
		$this->data = mysql_fetch_array($r, MYSQL_ASSOC);
	}
	
	function getName($maxlength){
		$name = $this->data['name'];
		if (strlen($this->data['vname']) > 2){
			$name = $this->data['vname'];
		}
		if ($this->data['mnr'] > 1){
			if (strlen($name) >= $maxlength){
				$name = substr($name, 0, $maxlength-2);
			}
			$name = $name . " " . $this->data['mnr'];
		}
		return substr($name, 0, $maxlength);
	}
}

class SWI_Spieler {
	private $data;
	private $results = array();
	
	function __construct($id){
		$r = mysql_query("SELECT id, mannschaft, zps, brettnr, vorname, nachname, dwz, elo, geburt FROM spieler WHERE id='$id'");
		if (mysql_num_rows($r) != 1){
			throw new Exception("SWI_Spieler failed to construct for id: ".$id);
		}
		$this->data = mysql_fetch_array($r, MYSQL_ASSOC);
	}

	private function get($field){
		if (!isset ($this->data[$field])){
			die ("no such field: ".$field);
		}
		return $this->data[$field];
	}
	
	private function getOptional($field){
		if (!isset ($this->data[$field])){
			return "";
		}
		return $this->data[$field];
	}
	
	function getMannschaft(){
		return $this->get("mannschaft");
	}
	
	function getId(){
		return $this->get("id");
	}
	
	function getName(){
		return $this->get('nachname') . ", " . $this->get('vorname');
	}
	
	function getGeburtsjahr(){
		$geb = explode (".", $this->getOptional("geburt"));
		$geb = $geb[count($geb)-1];
		$geb = substr("19$geb", -4);
		return strlen($geb) == 4 ? $geb : "";
	} 
	
	function getDwz(){
		return $this->getOptional("dwz");
	}
	
	function getElo(){
		return $this->getOptional("elo");
	}
	
	function getZPS(){
		$zps = explode("-", $this->getOptional("zps"));
		if (count($zps) == 2 && strlen($zps[0]) == 5 && strlen($zps[1]) > 0){
			return $zps;
		} else {
			return array("","");
		}
	}	
	
  /**
   * Adds the result to the player. $index needs to be the index of the game in that round, in order to support multiple
   * games in the same round. DEWIS requires games to be in the same round, therefore this has to be passed in by the caller.
   * Use getResults to check for the next available index.
   */
	function addErgebnis($runde, $index, $erg, $color, $gegner){
		if (!isset($this->results[$runde])){
			$this->results[$runde] = array();
		}
		if ( $erg == utf8_decode("½") ) $erg = "R";
		if ( $erg == "+" || $erg == "-" ) $color = ":";
		$this->results[$runde][$index] = array($erg, $color, $gegner);
		return count($this->results[$runde]);
	}
	
	function hasGames (){
		return count($this->results) > 0;
	}
	
	function getResults($runde){
		if (!isset($this->results[$runde])) {
			return array();
		}
		
		return $this->results[$runde];
	}
	
	function getHash(){
		return $this->get("brettnr") + $this->getMannschaft() * 100;
	}
	
	static function compare ($a, $b){
		$h1 = $a->getHash();
		$h2 = $b->getHash();
		if ($h1 == $h2) return 0;
		return $h1 < $h2 ? -1 : 1;
	}
}
	
class SWI_Export {
	private $spieler = array();  	  // id => SWI_Spieler
	private $index = array();		  // index => id
	private $indexReversed = array(); // id => index
	private $mannschaften = array();  // id => SWI_Mannschaft
	
	// gibt an, wie viele spiele je spieltag ein maximal gespielt hat (vor allem doppelrunde)
	private $maxGameCounts = array();
	
	function addStaffel ($id) {
		$r = mysql_query("SELECT runde, brett, spieler1, spieler2, ergebnis1, ergebnis2 FROM spielerpaarungen sp INNER JOIN paarungen p ON p.id=sp.paarung WHERE p.staffel=$_GET[staffel] AND sp.spieler1 IS NOT NULL AND sp.spieler2 IS NOT NULL");
		while ($paarung = mysql_fetch_array($r, MYSQL_ASSOC)){
      $this->addErgebnis($paarung['runde'], $paarung['brett'], $paarung['spieler1'], $paarung['spieler2'], $paarung['ergebnis1'], $paarung['ergebnis2']);
		}
	}
	
	private function addSpieler($id){
		if (!isset($this->spieler[$id])){
			$spieler = new SWI_Spieler($id);
			$this->addMannschaft($spieler->getMannschaft());
			$this->spieler[$id] = $spieler;
		}
	}

	private function addMannschaft($id){
		if (!isset($this->mannschaften[$id])){
			$this->mannschaften[$id] = new SWI_Mannschaft($id);
		}
	}
	
	function addErgebnis($runde, $brett, $s1, $s2, $erg1, $erg2){
		$this->addSpieler($s1);
		$this->addSpieler($s2);
		$c1 = $this->getColor($brett, true);
		$c2 = $this->getColor($brett, false);
    
    // Check if there are already results for this round registered on the player.
    $index = max(count($this->getSpieler($s1)->getResults($runde)), count($this->getSpieler($s2)->getResults($runde)));
		$gamecount1 = $this->getSpieler($s1)->addErgebnis($runde, $index, $erg1, $c1, $s2);
		$gamecount2 = $this->getSpieler($s2)->addErgebnis($runde, $index, $erg2, $c2, $s1);
		
		// Doppelrunden fix
		if (!isset($this->maxGameCounts[$runde])){
			$this->maxGameCounts[$runde] = 1;
		}
		$this->maxGameCounts[$runde] = max($gamecount1, $gamecount2, $this->maxGameCounts[$runde]);
	}

	function getColor($brett, $heim){
		$isGerade = $brett % 2 == 0;
		if (!$heim) $isGerade = !$isGerade;
		return $isGerade ? 'W' : 'B';
	}
	
	function createIndex (){
		$this->index = array();
		foreach ($this->spieler as $id => $spieler){
			if ($spieler->hasGames()){
				$this->index[] = $spieler;
			}
		}
		usort($this->index, array("SWI_Spieler", "compare"));
		
		$this->indexReversed = array();
		foreach ($this->index as $index => $spieler){
			$this->indexReversed[$spieler->getId()] = $index;
		}
	}

	function getIndex (){
		return $this->index;
	}
	
	function getMannschaft ($id) {
		if (!isset($this->mannschaften[$id])) 
			die("Mannschaft nicht gefunden: ".$id);
		return $this->mannschaften[$id];
	}

	function getSpieler ($id) {
		if (!isset($this->spieler[$id])) 
			die("Spieler nicht gefunden: ".$id);
		return $this->spieler[$id];
	}

	function getSpielerIndex ($id){
		if (!isset($this->indexReversed[$id])) 
			die("Spieler-Index nicht gefunden: ".$id);
		return $this->indexReversed[$id];
	}
	
	function getRounds(){
		return $this->maxGameCounts;
	}

}

class SWI_TextWriter {
  function __construct() {
	  $this->erg_allowed = array ("1", "0", "+", "-", "R");
  	$this->color_allowed = array ("B", "W", ":");
  }

	function sendHeaders(){
		header("Content-type: text/plain; charset=cp850");
	}
	
	function write($text){
		echo $text;
	}
	
	function encode($text){
    //return $text;
		return iconv("ISO-8859-1", "CP850", $text);
	}
	
	function newLine(){
		$this->write("\r\n");
	}
	
	function line($str){
		$this->write($str);
		$this->newLine();
	}
	
	function spaces($count){
		$this->write(str_repeat(" ", $count));
	}
	
	function strvalue($width, $str){
		$str = str_pad($str, $width, " ", STR_PAD_RIGHT);
		$this->write($this->encode($str));
	}
	
	function intvalue($width, $int){
		if ($int == 0) $int = "";
		if ($int != "" && !is_numeric($int)) die("integer expected, but got: ".$int);
		$str = str_pad($int, $width, " ", STR_PAD_LEFT);
		$this->write($str);
	}
	
	function erg($erg){
		if (!in_array($erg, $this->erg_allowed)){
			die("result expected, but got: ".$erg);
		}
		$this->write($erg);
	}

	function color($col){
		if (!in_array($col, $this->color_allowed)){
			die("color expected, but got: ".$erg);
		}
		$this->write($col);
	}
	
}


class SWI_Main extends SWI_TextWriter {
	private $ex;

	function main (){
		$this->sendHeaders();
		$this->createExporter();
		$info = $this->getTournamentInfo();

		$this->line($info->name);
		$this->newLine();
		$this->write("MR ");
		$this->intvalue(3, count($this->ex->getIndex()));
		$this->intvalue(3, array_sum($this->ex->getRounds()));
		$this->intvalue(3, 1); // ?!?
		$this->line(" #860#"); // ?!?

		$this->line(" ttt. rrr nnnnnnnnnnnnnnnnnnnnnnnnnnnnnnnn vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv lll ffffffffff pppppppppp gggggggg eeee dddd  zzzzz mmmm  ");
		foreach ($this->ex->getIndex() as $index => $spieler){
			$this->processPlayer($index, $spieler);
		}
		
		$this->line("###");
		$this->strvalue(12, "Name:");
		$this->line($info->name);
		$this->strvalue(12, "Ort:");
		$this->line("");
		$this->strvalue(12, "FIDE-Land:");
		$this->line("");
		$this->strvalue(12, "Datum(S):");
		$this->strvalue(21, $info->datumStart);
		$this->strvalue(12, "Datum(E):");
		$this->line($info->datumEnde);
		$this->strvalue(12, utf8_decode("Züge(1):"));
		$this->strvalue(21, "");
		$this->strvalue(12, utf8_decode("Züge(2):"));
		$this->strvalue(21, "");
		$this->strvalue(12, utf8_decode("Züge(3):"));
		$this->strvalue(21, "");
		$this->newLine();
		$this->write("Hauptschiedsrichter:");
		$this->write($this->encode($info->hauptSchiedsrichter));
		$this->newLine();
		$this->write("Weitere Schiedsrichter:");
		$this->write($this->encode($info->schiedsrichter));
		$this->newLine();
	}
	
	function processPlayer($index, $spieler){
		$this->spaces(1);
		$this->intvalue(3, $index+1);
		$this->write(".");
		$this->spaces(1+3+1);
		$this->strvalue(32, $spieler->getName());
		$this->spaces(1);
		$this->strvalue(32, $this->ex->getMannschaft($spieler->getMannschaft())->getName(32));
		$this->spaces(1+3+1+10+1+10+1);
		$this->intvalue(4, $spieler->getGeburtsjahr());
		$this->spaces(4); // Hack for Dewis, does not recognize gggg?!
		$this->spaces(1);
		$this->intvalue(4, $spieler->getElo());
		$this->spaces(1);
		$this->intvalue(4, $spieler->getDwz());
		$this->spaces(2);
		$zps = $spieler->getZPS();
		$this->strvalue(5, $zps[0]);
		$this->spaces(1);
		$this->intvalue(4, $zps[1]);
		$this->spaces(1);

		foreach ($this->ex->getRounds() as $round => $maxGames) {
			$results = $spieler->getResults($round);
			for ($i = 0; $i < $maxGames; ++$i) {
				if (isset($results[$i])) {
					$this->spaces(1);
					$this->erg($results[$i][0]);
					$this->color($results[$i][1]);
					$playerIndex = $this->ex->getSpielerIndex($results[$i][2]);
					$this->intvalue(3, $playerIndex + 1);
				} else {
					$this->spaces(1 + 1 + 1 + 3);
				}
			}
		}
				
		$this->newLine();
	}
	
	function createExporter (){
		global $globals;
		$this->ex = new SWI_Export();
		if ( isset ( $_GET ['staffel'] ) ){
			$this->ex->addStaffel($_GET['staffel']);
		} else {
			$r = mysql_query("SELECT id FROM staffeln WHERE turnier='$globals[tid]'");
			while ($s = mysql_fetch_array($r, MYSQL_ASSOC)){
				$this->ex->addStaffel($s['id']);
			}
		}
		$this->ex->createIndex();
	}

	function getTournamentInfo(){
		global $globals, $prefs;
		$staffel = $_GET['staffel'];
		
		$result = new stdClass();
		$result->name = $prefs['name'];
		$result->hauptSchiedsrichter = @reset(mysql_fetch_array(mysql_query(
			"select name from benutzer where id=$prefs[leiter]"
		)));
		$result->schiedsrichter = @reset(mysql_fetch_array(mysql_query(
			"select b.name from benutzer b join staffeln s on s.leiter=b.id where s.id=$staffel"
		)));		
		$result->datumStart = date('d.m.Y', strtotime(
			@reset(mysql_fetch_array(mysql_query(
			"select datum from viewStaffeltermine where id=$staffel order by datum asc limit 1"
		)))));
		$result->datumEnde = date('d.m.Y', strtotime(
			@reset(mysql_fetch_array(mysql_query(
			"select datum from viewStaffeltermine where id=$staffel order by datum desc limit 1"
		)))));
		
		/* Alle Staffelleiter
		$rsrc = mysql_query("select b.name from benutzer b join staffeln s on s.leiter=b.id where s.turnier=$prefs[id]");
		while ($name = (mysql_fetch_array($rsrc)))
			$names[] = reset($name);
		sort($names);
		$result->schiedsrichter = implode(', ', array_unique($names));
		*/

		return $result;
	}
}
	

	
	
$main = new SWI_Main();
$main->main();
