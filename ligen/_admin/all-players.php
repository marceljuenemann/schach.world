<?php    
header("Content-type: text/plain");
require_once ( "admin.inc.php" );
require_once ( "spieler.class.php" );
if ( $_GET ["auth"] != substr ( $globals ["masterpasswort"], 5, 5 ) )
	SED_Error ( "Keine Berechtigung!", true );

/*
id
mannschaft
zps
brettnr
vorname
nachname
titel
dwz
elo
geburt
geschlecht
nmSid
nmR

ZPS
Mgl_Nr
Status
Spielername
Spielername_G
Geschlecht
Spielberechtigung
Geburtsjahr
Letzte_Auswertung
DWZ
DWZ_Index
FIDE_Elo
FIDE_Titel
FIDE_ID
FIDE_Land
*/

$mapping = array(
	'dwz' => 'DWZ',
	'elo' => 'FIDE_Elo',
	'geburt' => 'Geburtsjahr',
	'geschlecht' => 'Geschlecht',
);
	
$staffel = 999999999;
$r_teams = mysql_query( "select * from mannschaften where staffel='$staffel'" );
while ($team = mysql_fetch_array($r_teams, MYSQL_ASSOC)) {
	$brett = 1;
	$r_players = mysql_query( "select * from dwz_spieler where zps='$team[zps]' and (status is null or status<>'P') order by dwz desc" );
	while ($player = mysql_fetch_array($r_players, MYSQL_ASSOC)) {
		$name = new SED_Spieler();
		$name->setName($player['Spielername']);
		$name->set('titel', $player['FIDE_Titel']);
		$values = array(
			'mannschaft' => $team['id'],
			'zps' => $team['zps'].'-'.$player['Mgl_Nr'],
			'brettnr' => $brett,
			'vorname' => $name->getDecoded('vorname'),
			'nachname' => $name->getDecoded('nachname'),
			'titel' => $name->getDecoded('titel'),
		);
		$brett++;
		foreach ($mapping as $to => $from) {
			$values[$to] = $player[$from];
		}
			
		$setter = array();
		foreach ($values as $key => $value) {
			$setter[] = "$key='$value'";
		}
			
		$sql = "INSERT INTO spieler SET ";
		$sql .= implode(', ', $setter);
		$sql .= ';';
		
		//echo "$sql\n";
		mysql_query($sql); 
	}
	
	echo "$team[name] (".($brett-1)." Spieler)\n";
}



