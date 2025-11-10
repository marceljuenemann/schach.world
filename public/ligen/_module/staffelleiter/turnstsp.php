<?
/* SL-Bereich: Spielplan
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage staffelleiter
 */

    require_once ( "login.inc.php");

  /////////////////////////////////////////////
  // SPEICHERUNG
  /////////////////////////////////////////////

  // Abgesendet
  if ( isset ( $_POST ['hidden_paarungen'] ) )
  {
    // Alte löschen
    if ( !mysql_query ( "DELETE FROM paarungen WHERE staffel=$admin[staffel] AND erg1 IS NULL AND erg2 IS NULL", $globals ['db'] ) )
      SED_Error ( "Konnte alten Spielplan nicht l&ouml;schen!", true );

    // Einfügen
    $paar = explode ( ";", $_POST ['hidden_paarungen'] );
    foreach ( $paar as $paarung )
    {
      $tmp = explode ( "~", $paarung );
      if ( count ( $tmp ) == 4 )
        mysql_query ( "INSERT INTO paarungen SET staffel=$admin[staffel], runde=$tmp[0], mannschaft1=$tmp[1], mannschaft2=$tmp[2]", $globals ['db'] );
    }

    // Cache leeren
    SED_Cache::clearAll ( $admin ['staffel'] );
    SED_Cache::clearTeam ( 0, SED_Cache::TEAM_SPIELPLAN );
    SED_Cache::clearTeam ( 0, SED_Cache::TEAM_ERGEBNISSE );

    // Refresh
    echo "Spielplan gespeichert.";
    echo "<meta http-equiv='refresh' content='0;URL=?admin=desktop-$admin[userid]-$admin[session]&staffel=$admin[staffel]' />";
    exit;
  }


  /////////////////////////////////////////////
  // ABFRAGEN UND LOGIK
  /////////////////////////////////////////////

  // Abfragen
  $rsrcTeamsAlle = mysql_query ( "SELECT m.id, g.lat, g.lon, m.staffel FROM mannschaften as m LEFT JOIN geodb as g ON g.plz=m.so_plz WHERE m.turnier=$globals[tid] ORDER BY m.name, m.mnr", $globals ['db'] );
  $rsrcTeamsStaffel = mysql_query ( "SELECT m.id, g.lat, g.lon FROM mannschaften as m LEFT JOIN geodb as g ON g.plz=m.so_plz WHERE m.staffel=$admin[staffel] ORDER BY m.name, m.mnr", $globals ['db'] );
  $rsrcPaarungen = mysql_query ( "SELECT runde, mannschaft1, mannschaft2 FROM paarungen WHERE staffel=$admin[staffel] AND erg1 IS NULL AND erg2 IS NULL", $globals ['db'] );
  $isRunning = SED_Value("SELECT COUNT(*) FROM paarungen WHERE staffel=? LIMIT 1", [$admin['staffel']]);

  // Überprüfung
  if ( !$rsrcTeamsStaffel || !mysql_num_rows ( $rsrcTeamsStaffel ) )
    SED_Error ( "Sie haben dieser Staffel noch keine Mannschaften zugeordnet! Dies geht &uuml;ber die Funktion Bearbeiten.", false );

  // Überlegen, was gemacht wird
  $showInfopage = !$isRunning && !isset ( $_GET ['modus'] );
  $showInfobox = $isRunning;
  $showSchnelleingabe = !$isRunning && isset ( $_GET ['modus'] ) && $_GET ['modus'] == "schnell";
  $showTabelle = !$isRunning && !$showInfopage && (mysql_num_rows ( $rsrcTeamsStaffel ) < 15 || $showSchnelleingabe);
  $showPaarungen = !$showInfopage;
  $showKM = $showTabelle;
  $showButtons = !$showInfopage;
  $usePaarungstafel = !$isRunning && isset ( $_GET ['modus'] ) && $_GET ['modus'] == "paarungstafel";


  /////////////////////////////////////////////
  // FORMULAR VORBEREITEN, INFOTEXTE
  /////////////////////////////////////////////

    echo "<div id='JsSecure' style='display: none; text-align: justify'>";
    echo "<form id='turnstsp_form' action='".SED_GenerateFormAction()."' method='post'><div>";

    // Über mögliche Modi informieren
    if ( $showInfopage )
    {
        echo "Sie haben folgende M&ouml;glichkeiten, den Spielplan in den Ergebnisdienst einzugeben:<br /><br />
            <a href='?admin=turnstsp-$admin[userid]-$admin[session]&staffel=$admin[staffel]&modus=paarungstafel'>Neuen Spielplan mit Hilfe einer Paarungstafel erstellen</a><br /><br />
            <a href='?admin=turnstsp-$admin[userid]-$admin[session]&staffel=$admin[staffel]&modus=einfach'>Vorhandenen Spielplan eingeben</a><br /><br />
            <a href='?admin=turnstsp-$admin[userid]-$admin[session]&staffel=$admin[staffel]&modus=schnell'>Vorhandenen Spielplan im Schnelleingabe-Modus eingeben</a><br /><br />
        ";
    }

    // Über bereits eingegeben Paarungen informieren
    if ( $showInfobox )
    {
        echo "<div class='sed_infomeldung'>Paarungen, deren Ergebnisse bereits eingegeben wurden, werden in dieser Ansicht nicht angezeigt! Um jene Paarungen zu bearbeiten, m&uuml;ssen Sie die Ergebnisse zun&auml;chst &uuml;ber die Paarungseinstellungen l&ouml;schen.</div>";
    }


  /////////////////////////////////////////////
  // DATEN FÜR JAVASCRIPT BEREITSTELLEN, PAARUNGSTAFEL
  /////////////////////////////////////////////

    $strPaarungen = $strMannschaften = $strZuordnungen = "";
    if ( $usePaarungstafel )
    {
        // Paarungen mit Standart-Paarungstafel erzeugen
        require_once ( "paarungstafel.inc.php" );
        $strPaarungen = Paarungstafel ( mysql_num_rows ( $rsrcTeamsStaffel ) );

        // Die Standart-Bezeichnungen durch die Mannschaftsid ersetzen
        mysql_data_seek ( $rsrcTeamsStaffel, 0 );
        for ( $i = 1; $team = mysql_fetch_assoc ( $rsrcTeamsStaffel ); ++$i )
            $strPaarungen = str_replace ( "m$i~", "$team[id]~", $strPaarungen );
    }
    else
    {
        // Vorhandene Paarungen in lesbares Format wandeln
        while ( $tmp = mysql_fetch_array ( $rsrcPaarungen, MYSQL_NUM ) )
            $strPaarungen .= "$tmp[0]~$tmp[1]~$tmp[2]~;";
    }

    // Mannschaften und Zuordnungen generieren
    mysql_data_seek ( $rsrcTeamsAlle, 0 );
    mysql_data_seek ( $rsrcTeamsStaffel, 0 );
    while ( $team = mysql_fetch_array ( $rsrcTeamsAlle, MYSQL_ASSOC ) )
        $strMannschaften .= "$team[id]~" . $globals ['teams'][$team ['id']] . "~$team[lat]~$team[lon];";
    for ( $i = 1; $team = mysql_fetch_array ( $rsrcTeamsStaffel, MYSQL_ASSOC ); ++$i )
        $strZuordnungen .= "$team[id]~$i;";

    // In Javascript und nach dem Absenden verfügbar machen
    echo "<input type='hidden' name='hidden_mannschaften' value='$strMannschaften' />";
    echo "<input type='hidden' name='hidden_zuordnungen' value='$strZuordnungen' />";
    echo "<input type='hidden' name='hidden_paarungen' value='$strPaarungen' />";


  /////////////////////////////////////////////
  // SCHNELLEINGABE
  /////////////////////////////////////////////

  if ( $showSchnelleingabe )
  {
    // Überschriften und Spieltag-Auswahl
    echo "<span class='sed_hl2'>Schnelleingabe</span><br /><br />";
    echo "Zur Paarungseingabe geben Sie einfach hintereinander die Mannschaftsnummern (s. Kreuztabelle) an. Dabei werden die Nummern nicht durch Leerzeichen getrennt und in jeder Zeile genau ein Spieltag eingegeben. Die Nummer 10 wird als A angegeben, 11 als B usw.<br /><br />";
    echo "<textarea id='SE' cols='60' rows='5'>4123\r\n9A7856</textarea><br /><br />";
    echo "<input type='button' class='sed_submit' value='Verarbeiten' onclick='Schnelleingabe();' /><br /><br />";
  }


  /////////////////////////////////////////////
  // PAARUNGEN
  /////////////////////////////////////////////

  if ( $showPaarungen )
  {
    // Überschriften und Spieltag-Auswahl
    echo "<span class='sed_hl2'>Paarungen</span><br /><br />";
    echo "<table class='sed_tabelle' cellspacing='0' cellpadding='5'>";
    echo "<tr><td colspan='2' style='background:#eeeeee'><a href='javascript:var dummy=1;' onclick='document.getElementById(\"PV-1-S\").selectedIndex--;PvGenerate();'><img src='$globals[systemicons]pre.gif' alt='Vorheriger' class='sed_admin_icon' /></a> <select id='PV-1-S' onchange='PvGenerate();'>";
    for ( $j = 1; $j <= SED_GetRundenzahl ( $admin ["staffel"] ); ++$j )
      echo "<option value='$j'>$j. Spieltag</option>";
    echo "</select> <a href='javascript:var dummy=1;' onclick='document.getElementById(\"PV-1-S\").selectedIndex++;PvGenerate();'><img src='$globals[systemicons]next.gif' alt='N&auml;chster' class='sed_admin_icon' /></a></td></tr>";

    // Mannschaftsauswahl
    $options = "";
    mysql_data_seek ( $rsrcTeamsStaffel, 0 );
    mysql_data_seek ( $rsrcTeamsAlle, 0 );
    while ( $team = mysql_fetch_array ( $rsrcTeamsStaffel, MYSQL_ASSOC ) )
      $options .= "<option value='$team[id]'>" . $globals ['teams'][$team ['id']] . "</option>";
    $options .= "<option value='0'>----------------</option>";
    while ( $team = mysql_fetch_array ( $rsrcTeamsAlle, MYSQL_ASSOC ) )
      $options .= "<option value='$team[id]'>" . $globals ['teams'][$team ['id']] . "</option>";

    // Buttons
    echo "<tr id='PV-3'><td colspan='2' style='background: #eeeeee;'><select id='PV-3-A'>$options</select> gegen <select id='PV-3-B'>$options</select> <input type='button' value='Hinzuf&uuml;gen' onclick='OnAddPaar();' class='sed_submit' /></td></tr>";
    echo "</table><br /><br />";
  }


  /////////////////////////////////////////////
  // KREUZTABELLE
  /////////////////////////////////////////////

  if ( $showTabelle )
  {
    // Infotexte
    echo "<span class='sed_hl2'>Kreuztabelle</span><br /><br />";
    echo "Die Kreutabelle zeigt, an welchem Spieltag und wo zwei Mannschaften gegeneinander spielen. &Uuml;ber die Pfeile k&ouml;nnen Sie zwei Mannschaften tauschen, ohne die Paarungstafel an sich zu &auml;ndern.<br /><br />";

    // Kreuztabelle - Erste Zeile
    echo "<table class='sed_tabelle' cellspacing='0' cellpadding='3'><tr><th></th><th>Name</th>";
    for ( $i = 1; $i <= mysql_num_rows ( $rsrcTeamsStaffel ); ++$i )
      echo "<th>$i</th>";
    echo "<th>Heim.</th><th></th></tr>";

    // Kreuztabelle - Inhalt
    mysql_data_seek ( $rsrcTeamsStaffel, 0 );
    for ( $i = 1; $team = mysql_fetch_assoc ( $rsrcTeamsStaffel ); ++$i )
    {
      // Nummer und Mannschaft ausgeben
      echo "<tr><td>$i.</td><td id='KT-$i-N' class='l'><a href='javascript:var dummy=1;' onclick='QuickAdd($team[id]);'>" . $globals ['teams'][$team ['id']] . "</a>&nbsp;&nbsp;</td>";

      // Zelle ausgeben. Wird von Javascript gefüllt
      for ( $j = 1; $j <= mysql_num_rows ( $rsrcTeamsStaffel ); ++$j )
      {
        $content = $i == $j ? "xxx" : "";
        echo "<td id='KT-$i-$j'>$content</td>";
      }

      // Anzahl Heimspiele und Verschiebe-Buttons
      echo "<td id='KT-$i-H'>0</td>";
      echo "<td><a href='javascript:var dummy=1;' onclick='OnSwitch(".($i-1).",$i);'><img src='$globals[systemicons]up.gif' alt='Hoch' class='sed_admin_icon' /></a>";
      echo "<a href='javascript:var dummy=1;' onclick='OnSwitch($i,".($i+1).");'><img src='$globals[systemicons]down.gif' alt='Runter' class='sed_admin_icon' /></a></td></tr>";
    }
    echo "</table><br /><br />";
  }


  /////////////////////////////////////////////
  // KM - Ausgabe
  /////////////////////////////////////////////

  if ( $showKM )
  {
    // Entfernungstabelle
    echo "<span class='sed_hl2'>Entfernungs-Ausgleich</span><br /><br />";
    echo "<table><tr><td><table id='KM' style='display: none' class='sed_tabelle' cellspacing='0' cellpadding='3'>";
    echo "<tr><th>Name</th><th>km</th><th>Diff.</th></tr>";
    mysql_data_seek ( $rsrcTeamsStaffel, 0 );
    for ( $i = 1; $team = mysql_fetch_array ( $rsrcTeamsStaffel, MYSQL_ASSOC ); ++$i )
      echo "<tr><td id='KM-$i-N' class='l'>" . $globals ['teams'][$team ['id']] . "&nbsp;&nbsp;</td><td id='KM-$i-1'></td><td id='KM-$i-2'></td></tr>";
    echo "</table><br /><br /></td>";

    // Deutschlandkarte
    $maplink = "";
    mysql_data_seek ( $rsrcTeamsStaffel, 0 );
    while ( $team = mysql_fetch_array ( $rsrcTeamsStaffel, MYSQL_ASSOC ) )
      $maplink .= "$team[lat]-$team[lon]-;";
    echo "<td>&nbsp;&nbsp;&nbsp;<img src='$globals[basedir]/_inc/extern/MelliMap.php?locations=$maplink' alt='' style='height: 242px' /></td></tr></table>";
  }


  /////////////////////////////////////////////
  // BUTTONS UND JAVASCRIPT
  /////////////////////////////////////////////

  if ( $showButtons )
  {
    // Absenden
    ?>
    <span class='sed_hl2'>Spielplan speichern</span><br /><br />
    Sind Sie sicher, dass Sie den alten Spielplan unwideruflich mit den neuen Daten &uuml;berschreiben m&ouml;chten?<br /><br />
    <input type='submit' class='sed_submit' value='Speichern' />
    <input type='button' class='sed_submit' value='Abbrechen' onclick="<? echo "location='?admin=desktop-$admin[userid]-$admin[session]';"; ?>" />
    <?
  }
  echo "</div></form>";
?>

<script type='text/javascript'><!--

/********************

GLOBALE VARIABLEN

hidden_mannschaften       Link auf hidden Feld mit Mannschaftsinfos
hidden_zuordnungen        Link auf hidden Feld mit Zuordnungen der Mannschaften zur KT
hidden_paarungen          Link auf hidden Feld mit Paarungen
array_mannschaften        Array ( mid => Array ( name, lat, lon ) )
array_zuordnungen         Array ( mid => zuord )


FUNKTIONEN

AddPaar               Fuegt eine Paarung in das Hidden Feld und die KT ein
DelPaar               Loescht eine Paarung aus dem Hidden Feld und der KT
PaarViewAdd           Fuegt eine Paarung in die Paarungsanzeige ein
PaarViewDel           Loescht eine Paarung aus der Paarungsanzeige
PaarViewClear         Loescht die gesamte Paarungsanzeige
PaarViewGenerate      Generiert die Paarungsanzeige, liest dazu Runde aus select aus
ScfLoad               Liest alle Paarungen aus dem Hidden Feld in die KT ein
KmClear               Versteckt KM
KmDistance            Berechnet die Entfernung zweier Orte aus Breiten- und Laengengrad
KmCalc                Berechnet KM
OnAddPaar             Bei Klick auf Hinzufuegen
OnDelPaar             Bei Klick auf Loeschsymbol
OnSave                Bei Klick auf Speichern
OnSwitch              Bei Klick auf einen der Pfeile
QuickAdd              Mannschaftsauswahl durch Klick auf den Namen
Schnelleingabe        Verarbeitung im Schnelleingabe-Modus

*******************/

// Hiddens als Objekt referenzieren
var hidden_mannschaften = document.getElementsByName ( "hidden_mannschaften" ) [0];
var hidden_zuordnungen = document.getElementsByName ( "hidden_zuordnungen" ) [0];
var hidden_paarungen = document.getElementsByName ( "hidden_paarungen" ) [0];

// Mannschaften als Array laden
var array_mannschaften = new Array ();
var $arrMann = hidden_mannschaften.value.split ( ";" );
for ( $i = 0; $i < $arrMann.length; ++$i )
{
  var $tmp = $arrMann [$i].split ( "~" );
  array_mannschaften [$tmp [0]] = new Array ( $tmp [1], $tmp [2], $tmp [3] );
}

// Zuordnungen als Array laden
var array_zuordnungen = new Array ();
var $arrZuord = hidden_zuordnungen.value.split ( ";" );
for ( $i = 0; $i < $arrZuord.length; ++$i )
{
  var $tmp = $arrZuord [$i].split ( "~" );
  array_zuordnungen [$tmp [0]] = $tmp [1];
}

// Paarungen einlesen
ScfLoad ( hidden_paarungen.value );

// GUI
KmClear ();
PvGenerate ();

// Optimization
$optCurrentValue = 0;
$optBestSolution = null;
$optFixedTeams = []; // [{mid: 3132, pos: 4}];

function Optimize ()
{
	$teams = new Array();
	for (var $team in array_zuordnungen) {
		if (!array_zuordnungen.hasOwnProperty($team)) continue;
		if ($team === 'length') continue;
		$teams[$team] = false;
	}
	$bestValue = OptBacktrack ($teams, 1, 999999999);

	var max = <? echo mysql_num_rows ( $rsrcTeamsStaffel ); ?>;
	for (var i = 1; i <= max; ++i){
		for (var team in $optBestSolution){
			if ($optBestSolution[team] === i){
				OptMove(team, i);
			}
		}
	}
	return $bestValue;
}

function OptBacktrack ( $teams, $pos, $bestValue )
{
	var max = <? echo mysql_num_rows ( $rsrcTeamsStaffel ); ?>;
	if ($pos >= max){
		if ($optCurrentValue < $bestValue){
			$bestValue = $optCurrentValue;
			$optBestSolution = array_zuordnungen.slice(0);
		}
		return $bestValue;
	}
	
	for (var $team in $teams){
		if (!$teams.hasOwnProperty($team)) continue;
		if ($team === 'length') continue;
		if ($team === '') continue;
		if ($teams[$team]) continue;
		
		$valid = true;
		for (var i = 0; i < $optFixedTeams.length; ++i) {
			if ($team == $optFixedTeams[i].mid && $pos !== $optFixedTeams[i].pos) $valid = false;
			if ($team != $optFixedTeams[i].mid && $pos === $optFixedTeams[i].pos) $valid = false;
		}
		
		if ($valid) {
			$teams[$team] = true;
		
			OptMove($team, $pos);
			$bestValue = OptBacktrack($teams, $pos+1, $bestValue);

			$teams[$team] = false;
		}
	}
	return $bestValue;
}

function OptMove ( $team, $pos )
{
	var max = <? echo mysql_num_rows ( $rsrcTeamsStaffel ); ?>;
	for (var i = $pos; i <= max; i += 1){
		for (var j = i; j > $pos; j -= 1){
			OnSwitch(j-1, j);
		}
		if (array_zuordnungen[$team] == $pos) return;
	}
}

function AddPaar ( $runde, $teamA, $teamB )
{
  // Zu hidden var hinzufügen
  hidden_paarungen.value += $runde + "~" + $teamA + "~" + $teamB + "~;";

  // In Kreuztabelle einfügen
  KtAdd ( $runde, array_zuordnungen [$teamA], array_zuordnungen [$teamB] );
}

function DelPaar ( $runde, $teamA, $teamB )
{
  // Aus hidden var entfernen
  hidden_paarungen.value = hidden_paarungen.value.replace ( new RegExp ( $runde + "~" + $teamA + "~" + $teamB + "~;", "" ), "" );

  // Zuordnungen erhalten
  $zuordA = array_zuordnungen [$teamA];
  $zuordB = array_zuordnungen [$teamB];

  if ( document.getElementById ( "KT-" + $zuordA + "-" + $zuordB ) )
  {
    // Aus Kreuztabelle entfernen
    var $objA = document.getElementById ( "KT-" + $zuordA + "-" + $zuordB );
    var $objB = document.getElementById ( "KT-" + $zuordB + "-" + $zuordA );
    $objA.innerHTML = $objA.innerHTML.replace ( new RegExp ( $runde + "H", "" ), " " );
    $objB.innerHTML = $objB.innerHTML.replace ( new RegExp ( $runde + "G", "" ), "" );

    // Heimspiel abziehen
    document.getElementById ( "KT-" + $zuordA + "-H" ).innerHTML = parseInt ( document.getElementById ( "KT-" + $zuordA + "-H" ).innerHTML ) - 1;
  }
}

function KmClear ()
{
  // Tabelle verstecken
  if ( document.getElementById ( "KM" ) )
      document.getElementById ( "KM" ).style.display = "none";
  $optCurrentValue = 0;
  KmCalc ();
}

function KmCalc ()
{
  // Felder leeren
  for ( $i = 1; $obj = document.getElementById ( "KM-" + $i + "-1" ); ++$i )
  {
    $obj.innerHTML = "0";
  }

  // Alle Paarungen durchgehen
  var $arrPaar = hidden_paarungen.value.split ( ";" );
  var $average_sum = 0;
  for ( $i = 0; $i < $arrPaar.length; ++$i )
  {
    var $tmp = $arrPaar [$i].split ( "~" );

    // Eintrag vorhanden?
    if ( array_mannschaften [$tmp [1]] )
    {
      // Distanz berechnen
      var $dist = KmDistanceBetween ( array_mannschaften [$tmp [1]][1],
                                      array_mannschaften [$tmp [1]][2],
                                      array_mannschaften [$tmp [2]][1],
                                      array_mannschaften [$tmp [2]][2] );

      // In Tabelle einfügen
      var $obj = document.getElementById ( "KM-" + array_zuordnungen [$tmp [2]] + "-1" );
      if ( $obj )
      {
        // Jetzt wirds ernst
        if ( !parseInt ( $obj.innerHTML ) )
          $obj.innerHTML = "0";
        $obj.innerHTML = parseInt ( $obj.innerHTML ) + $dist;

        // Durchschnittsberechnung
        $average_sum += $dist;
      }
    }
  }

  // Abweichungungen berechnen
  var $average = $average_sum / <? echo mysql_num_rows ( $rsrcTeamsStaffel ); ?>;
  for ( $i = 1; $obj = document.getElementById ( "KM-" + $i + "-1" ); ++$i )
  {
    $temp = parseInt ( $obj.innerHTML );
	$diff = Math.abs ( $temp - $average );
	$optCurrentValue = ($diff > $optCurrentValue ? $diff : $optCurrentValue);
    if ( $temp ) document.getElementById ( "KM-" + $i + "-2" ).innerHTML = Math.round ( $temp - $average );
  }

  // Anzeigen
  if ( document.getElementById ( "KM" ) )
      document.getElementById ( "KM" ).style.display = "";
}

function KmDistanceBetween ( $g1lat, $g1lon, $g2lat, $g2lon )
{
  // Akzeptiere die Formel!
  return Math.round ( KmRad2Deg ( Math.acos( Math.sin ( KmDeg2Rad ( $g1lat ) ) * Math.sin ( KmDeg2Rad ( $g2lat ) )
         + Math.cos ( KmDeg2Rad ( $g1lat ) ) * Math.cos ( KmDeg2Rad ( $g2lat ) )
         * Math.cos ( KmDeg2Rad ( $g1lon - $g2lon ) ) ) * 60 * 1.85201 ), 0 );
}


function KmDeg2Rad ( $x )
{
  return Math.PI * $x / 180;
}

function KmRad2Deg ( $x )
{
  return 180 * $x / Math.PI;
}

function KtAdd ( $runde, $zuordA, $zuordB )
{
  if ( document.getElementById ( "KT-" + $zuordA + "-" + $zuordB ) )
  {
    document.getElementById ( "KT-" + $zuordA + "-" + $zuordB ).innerHTML += $runde + "H";
    document.getElementById ( "KT-" + $zuordB + "-" + $zuordA ).innerHTML += $runde + "G";
    document.getElementById ( "KT-" + $zuordA + "-H" ).innerHTML = parseInt ( document.getElementById ( "KT-" + $zuordA + "-H" ).innerHTML ) + 1;
  }
}

function OnAddPaar()
{
  // Werte sammeln
  var $runde = document.getElementById ( "PV-1-S" ).value;
  var $objA = document.getElementById ( "PV-3-A" );
  var $objB = document.getElementById ( "PV-3-B" );
  var $teamA = $objA.value;
  var $teamB = $objB.value;
  var $nameA = $objA.options [$objA.selectedIndex].text;
  var $nameB = $objB.options [$objB.selectedIndex].text;

  // Überprüfung
  if ( $runde < 1 || $teamA < 1 || $teamB < 1 || $teamA == $teamB )
    return false;

  // Hinzufügen zu SCF und KT
  AddPaar ( $runde, $teamA, $teamB );

  // GUI
  PvAdd ( $runde, $teamA, $teamB );
  KmClear ();
}

function OnDelPaar ( $runde, $teamA, $teamB )
{
  // Aus SCF und KT löschen
  DelPaar ( $runde, $teamA, $teamB );

  // GUI
  PvDel ( $runde, $teamA, $teamB );
  KmClear ();
}

function OnSwitch ( $zuordA, $zuordB )
{
  // In Array laden
  var $arrTemp = new Array ();
  var $arrZuord = hidden_zuordnungen.value.split ( ";" );
  for ( $i = 0; $i < $arrZuord.length; ++$i )
  {
    var $tmp = $arrZuord [$i].split ( "~" );
    $arrTemp [$tmp [1]] = $tmp [0];
  }

  // Validierung
  if ( !$arrTemp [$zuordA] || !$arrTemp [$zuordB] )
    return;

  // KT ändern
  tmp = document.getElementById ( "KT-" + $zuordA + "-N" ).innerHTML;
  document.getElementById ( "KT-" + $zuordA + "-N" ).innerHTML = document.getElementById ( "KT-" + $zuordB + "-N" ).innerHTML;
  document.getElementById ( "KT-" + $zuordB + "-N" ).innerHTML = tmp;

  // KM ändern
  document.getElementById ( "KM-" + $zuordA + "-N" ).innerHTML = array_mannschaften [ $arrTemp [$zuordB] ][0] + "&nbsp;&nbsp;";
  document.getElementById ( "KM-" + $zuordB + "-N" ).innerHTML = array_mannschaften [ $arrTemp [$zuordA] ][0] + "&nbsp;&nbsp;";

  // Paarungen ändern (Hinweis: Funktioniert nur, wenn mid > runde
  hidden_paarungen.value = hidden_paarungen.value.replace ( new RegExp ( "~" + $arrTemp [$zuordA] + "~", "g" ), "~temp~" );
  hidden_paarungen.value = hidden_paarungen.value.replace ( new RegExp ( "~" + $arrTemp [$zuordB] + "~", "g" ), "~" + $arrTemp [$zuordA] + "~" );
  hidden_paarungen.value = hidden_paarungen.value.replace ( new RegExp ( "~temp~", "g" ), "~" + $arrTemp [$zuordB] + "~" );

  // Globales Array ändern
  array_zuordnungen [ $arrTemp [$zuordA] ] = $zuordB;
  array_zuordnungen [ $arrTemp [$zuordB] ] = $zuordA;

  // Zuordnungen ändern
  hidden_zuordnungen.value = hidden_zuordnungen.value.replace ( new RegExp ( "~" + $zuordA + ";", "" ), "~temp;" );
  hidden_zuordnungen.value = hidden_zuordnungen.value.replace ( new RegExp ( "~" + $zuordB + ";", "" ), "~" + $zuordA + ";" );
  hidden_zuordnungen.value = hidden_zuordnungen.value.replace ( new RegExp ( "~temp;", "" ), "~" + $zuordB + ";" );

  // GUI
  KmClear ();
  PvGenerate ();
}

function PvAdd ( $runde, $teamA, $teamB )
{
  // Elemente erstellen
  var $objTR = document.createElement ( "tr" );
  var $objTD1 = document.createElement ( "td" );
  var $objTD2 = document.createElement ( "td" );
  var $objA = document.createElement ( "a" );
  var $objIMG = document.createElement ( "img" );

  // Zelle 1 (Text)
  $objTD1.appendChild ( document.createTextNode ( array_mannschaften [$teamA][0] + " - " + array_mannschaften [$teamB][0] ) );
  $objTD1.style.textAlign = "left";

  // Zelle 2 (Bild mit Link)
  $objIMG.setAttribute ( "src", "<? echo "$globals[systemicons]desk_loeschen.png"; ?>" );
  $objIMG.setAttribute ( "alt", "Paarung l&ouml;schen" );
  $objIMG.style.border = "none";
  $objA.setAttribute ( "href", "javascript:OnDelPaar("+$runde+", "+$teamA+", "+$teamB+");" );
  $objA.appendChild ( $objIMG );
  $objTD2.appendChild ( $objA );

  // Einfügen
  $objTR.appendChild ( $objTD1 );
  $objTR.appendChild ( $objTD2 );
  $objTR.setAttribute ( "id", "PV-2-" + $runde + "-" + $teamA + "-" + $teamB );
  var $objPV3 = document.getElementById ( "PV-3" );
  $objPV3.parentNode.insertBefore ( $objTR, $objPV3 );
}

function PvClear ()
{
  var $objPV3 = document.getElementById ( "PV-3" );
  while ( $objPV3.parentNode.childNodes.length > 2 )
    $objPV3.parentNode.removeChild ( $objPV3.previousSibling );
}

function PvDel ( $runde, $teamA, $teamB )
{
  var $obj = document.getElementById ( "PV-2-" + $runde + "-" + $teamA + "-" + $teamB );
  $obj.parentNode.removeChild ( $obj );
}

function PvGenerate ()
{
  // Spieltag auslesen
  var $runde = document.getElementById ( "PV-1-S" ).value;

  // Erst einmal leeren
  PvClear ();

  // hidden spliten
  var $zuordA, $zuordB;
  var $arrPaar = hidden_paarungen.value.split ( ";" );
  for ( $i = 0; $i < $arrPaar.length; ++$i )
  {
    var $tmp = $arrPaar [$i].split ( "~" );
    if ( $tmp [0] == $runde )
      PvAdd ( $runde, $tmp [1], $tmp [2] );
  }
}

function ScfLoad ( $paar )
{
  // Alle Paarungen durchgehen
  var $arrPaar = $paar.split ( ";" );
  for ( $i = 0; $i < $arrPaar.length; ++$i )
  {
    // In KT hinzufügen
    var $tmp = $arrPaar [$i].split ( "~" );
    KtAdd ( $tmp [0], array_zuordnungen [$tmp [1]],  array_zuordnungen [$tmp [2]] );
  }
}

var QuickAdd_static = 0;
function QuickAdd ( $mid )
{
    // Erste Mannschaft
    if ( !QuickAdd_static )
    {
        // Merken
        QuickAdd_static = $mid;

        // Bei Pv Auswählen
        var $objA = document.getElementById ( "PV-3-A" );
        $objA.value = $mid;
    }
    else
    {
        // Bei nöchsten Mal von vorne
        QuickAdd_static = 0;

        // Bei Pv Auswählen
        var $objB = document.getElementById ( "PV-3-B" );
        $objB.value = $mid;

        // Einfügen
        OnAddPaar ();
    }
}

function Schnelleingabe ()
{
    // In Array laden ( mid => nummer )
    var $arrMIDs = new Array ();
    var $arrZuord = hidden_zuordnungen.value.split ( ";" );
    for ( $i = 0; $i < $arrZuord.length; ++$i )
    {
        var $tmp = $arrZuord [$i].split ( "~" );
        $arrMIDs [$tmp [1]] = $tmp [0];
    }

    // Zeilen auslesen und in Hexadezimal umwandeln
    zeilen = document.getElementById ("SE").value.replace ( /\r/, "" ).split ( "\n" );

    // Die Spieltage / Zeilen durchgehen
    for ( i = 0; zeilen [i]; ++i )
    {
        // Spieltag auswählen
        document.getElementById ( "PV-1-S" ).value = i+1;

        // Die einzelnen Zeichen durchgehen
        for ( j = 0; j < zeilen [i].length; ++j ){
            id = parseInt(zeilen [i][j], 30).toString(10)
            QuickAdd ( $arrMIDs [ id ] );
        }
    }

    // Anzeige aktualisieren
    PvGenerate ();
    KmClear ();
}

--></script>

</div>
<div id='JsSecure2'>
  <span class='sed_hl2'>Seite wird geladen...</span><br />
  <br /><br />
  <br /><br />
  Sollte die Seite nach f&uuml;nf Sekunden noch nicht angezeigt werden,
  dann haben Sie Javascript nicht aktiviert. Informieren Sie
  sich, wie Sie <a href='http://www.gailer-net.de/html/javascript.html#b55' target='_blank'>Javascript in Ihrem Browser aktivieren</a>, und laden
  Sie diese Seite neu.
</div>

<script type="text/javascript"><!--

  document.getElementById ( 'JsSecure' ).style.display = "block";
  document.getElementById ( 'JsSecure2' ).style.display = "none";

--></script>
