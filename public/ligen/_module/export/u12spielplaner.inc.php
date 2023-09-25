<?php

header("Content-type: text/plain");
echo "welcome!\n";

global $games, $teams;

$teams = array (); // $team => $location (where is $team playing this round?)
$games = array (); // $location => $pid  (which games are at the location?)
$loc = 1;

function doPrint(){
	global $teams;
	global $games;
	foreach ($games as $loc1 => $pids){
		echo '"'.implode('-', $pids).'"';
		foreach ($teams as $team => $loc2){
			if ($loc2 == $loc1){
				// unterdrückt zweite mannschaften (nimmt an, dass die immer partner sind)
				if (substr($team, -1) == 1) {
					echo ',"'.$team.'"';
				}
			}
		}
		echo "\n";
	}
	$teams = array();
	$games = array();
}

function doJoin($from, $to){	
	if ($from == $to) return;
	global $teams, $games;
	foreach ($games[$from] as $pid){
		$games[$to][] = $pid;
	}
	unset($games[$from]);
	foreach ($teams as $team => &$loc){
		if ($loc == $from){
			$loc = $to;
		}
	}
}

		
$lastR = 1;
$r = mysql_query("SELECT paarungen.id, m1.name n1, m1.mnr nr1, m1.id mn1, m2.name n2, m2.mnr nr2, m2.id mn2, runde FROM paarungen JOIN mannschaften m1 ON m1.id=mannschaft1 JOIN mannschaften m2 ON m2.id=mannschaft2 WHERE paarungen.staffel='$_GET[staffel]' ORDER BY runde", $globals['db']);
while ($p = mysql_fetch_array($r, MYSQL_ASSOC)){
	extract($p);
	$m1 = "$mn1-$n1$nr1";
	$m2 = "$mn2-$n2$nr2";
	
	if ($lastR != $runde){
		doPrint();
		$lastR = $runde;
	}
	
	$location = null;
	if (isset($teams[$m1])){
		// $m1 is already playing at $teams[$m1], so this game has to be there, too
		$location = $teams[$m1];
		
		// if $m2 also had the location determined already, we have to join them
		if (isset($teams[$m2])){
			doJoin($teams[$m2], $location);
		}
	} else if (isset($teams[$m2])){
		$location = $teams[$m2];
	} else {
		// Location is not determined yet, let's open a new location!
		$location = $loc++;
	}
	
	$teams[$m1] = $location;
	$teams[$m2] = $location;
	$games[$location][] = $id;
}

doPrint();


