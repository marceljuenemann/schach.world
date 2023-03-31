<?
/* Turnierdaten laden
 *
 * Findet die ID des gewählten Turniers heraus und lädt einige grund-
 * legende Daten, wie Staffeln und Mannschaften.
 * @global prefs
 *
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 *
 * @package schach-ergebnisdienst
 * @subpackage main
 */

  // Felder aus Turnier-Tabelle in $prefs speichern
  if ( isset ( $globals ['tid'] ) )
    $query = "SELECT t.* FROM turniere as t WHERE t.id=$globals[tid]";
  elseif ( isset ( $_GET ['tid'] ) )
    $query = "SELECT t.* FROM turniere as t WHERE t.id=$_GET[tid]";
  elseif ( isset ( $_GET ['dir'] ) )
    $query = "SELECT t.* FROM turniere as t WHERE t.directory='$_GET[dir]'";
  elseif ( isset ( $_GET ['staffel'] ) )
    $query = "SELECT t.* FROM staffeln as s INNER JOIN turniere as t ON t.id=s.turnier WHERE s.id=$_GET[staffel]";
  elseif ( isset ( $_GET ['mid'] ) )
    $query = "SELECT t.* FROM mannschaften as m INNER JOIN staffeln as s ON s.id=m.staffel INNER JOIN turniere as t ON t.id=s.turnier WHERE m.id=$_GET[mid]";
  elseif ( isset ( $_GET ['mannschaft'] ) )
    $query = "SELECT t.* FROM mannschaften as m INNER JOIN staffeln as s ON s.id=m.staffel INNER JOIN turniere as t ON t.id=s.turnier WHERE m.id=$_GET[mannschaft]";
  elseif ( isset ( $_GET ['spieler'] ) )
    $query = "SELECT t.* FROM spieler as sp INNER JOIN mannschaften as m ON m.id=sp.mannschaft INNER JOIN staffeln as s ON s.id=m.staffel INNER JOIN turniere as t ON t.id=s.turnier WHERE sp.id=$_GET[spieler]";
  elseif ( isset ( $_GET ['p'] ) )
    $query = "SELECT t.* FROM paarungen as p INNER JOIN staffeln as s ON s.id=p.staffel INNER JOIN turniere as t ON t.id=s.turnier WHERE p.id=$_GET[p]";
  else
    SED_Error ( "Es konnte kein passendes Turnier gefunden werden!", true );
  $prefs = mysql_fetch_array ( mysql_query ( $query, $globals ['db'] ), MYSQL_ASSOC );

  // Fehler?
  if ( !is_array ( $prefs ) )
    SED_Error ( "Das Turnier scheint nicht zu existieren!", true );

  // Temporary hack: Disable Rundmail sign up since it's being used for spam mails.
  $prefs['sysKeinNewsletter'] = 1;

  // Für $globals [tid] sorgen
  $globals ['tid'] = $prefs ['id'];

    // Template berechnen
    if ( !isset ( $globals ['templatedir'] ) )
    {
        $template = "nsv";
        if ( isset ( $prefs ['template'] ) ) {
            $template = $prefs ['template'];
        }
        if ( SED_IsNsv2020() ) {
            $template = 'nsv2020';
        }
        $globals ['templatedir'] = "$globals[basedir]/_templates/$template";
    }

  // Staffeln
  $res = mysql_query ( "SELECT id, name FROM staffeln WHERE turnier=$globals[tid] ORDER BY sortid", $globals ['db'] );
  $globals ['staffeln'] = array ();
  while ( $temp = mysql_fetch_array ( $res, MYSQL_BOTH ) )
    $globals ['staffeln'][$temp ['id']] = $temp ['name'];

  // Mannschaften
  $res = mysql_query ( "SELECT m.id, IF(m.mnr>1,CONCAT(TRIM(m.name),' ',m.mnr),TRIM(m.name)) as name FROM mannschaften as m WHERE m.turnier=$globals[tid] ORDER BY name", $globals ['db'] );
  $globals ['teams'] = array ();
  while ( $temp = mysql_fetch_array ( $res, MYSQL_BOTH ) )
    $globals ['teams'][$temp ['id']] = $temp ['name'];


  // Liefert zu einem Spieltag den Timestamp
  function SED_GetTermin ( $runde, $staffel, $datumsformat = '%d.%m.%Y' )
  {
    global $globals;
    // Eigentlich gibt es mittlerweile ja viewStaffeltermine, allerdings funktioniert das hier auch, wenn keine Turniertermine festgelegt wurden, sondern nur Staffeltermine
    $x = mysql_fetch_array ( mysql_query ( "SELECT staffel, DATE_FORMAT(te.datum,'$datumsformat') as datum FROM termine as te WHERE te.turnier=$globals[tid] and te.runde=$runde and (te.staffel is null or te.staffel=$staffel) ORDER BY staffel DESC LIMIT 1", $globals ['db'] ), MYSQL_ASSOC );
    return $x ['datum'];
  }

  // Liefert die Anzahl der Runden einer Staffel
  function SED_GetRundenzahl ( $staffel = false, $feld = "runden" )
  {
    global $prefs;
    global $globals;

    // Versuchen die Staffel selbst zu finden
    if ( !$staffel )
        if ( isset ( $_GET ['staffel'] ) )
            $staffel = $_GET ['staffel'];
        else
            return $prefs [$feld];

    // Abfragen, ob die Anzahl vom Turnier-Standart abweicht
    return reset ( mysql_fetch_array ( mysql_query (
        "SELECT IF($feld IS NULL,".$prefs[$feld].",$feld) FROM staffeln WHERE id=$staffel", $globals ['db'] ) ) );
  }

  // Liefert die letzte Runde in der eine Paarung gesetzt ist
  function SED_GetLetzteRunde ( $staffel )
  {
    global $globals;
    return reset ( mysql_fetch_array ( mysql_query (
        "SELECT MAX( runde )  FROM paarungen WHERE staffel='$staffel'", $globals ['db'] ) ) );
  }

  // Liefert die Anzahl der Bretter einer Staffel
  function SED_GetBrettzahl ( $staffel = false )
  {
    return SED_GetRundenzahl ( $staffel, "brettzahl" );
  }

  // Gibt einen Link auf eine Mannschaft zurück
  function SED_TeamLink ( $id )
  {
    global $globals;
    return "<a href='?mannschaft=$id'>" . $globals ['teams'][$id] . "</a>";
  }

  // Liefert Links zu anderen Saisons
  function SED_GetSaisonLinks ()
  {
    global $prefs;
    $turnier = $prefs['directory'];
    $turnier = substr($turnier, 0, strlen($turnier) - 5);
    $ersteSaison = array(
      'sjbh' => 2010,
      'bezirk1' => 2008,
      'bezirk2' => 2015,
      'bezirk3' => 2010,
      'bezirk6' => 2007,
      'fbl' => 2013,
      'frl' => 2010,
      'jbln' => 2008,
      'nsj' => 2007,
      'nsv' => 2007,
      'pokal' => 2007,
    );
    if (!isset($ersteSaison[$turnier])) return array();
    $ersteSaison = $ersteSaison[$turnier];
    $letzteSaison = date('Y') - (date('n') < 7 ? 1 : 0);
    if ($turnier === 'fbl') $letzteSaison = 2018;

    $result = array();
    for ($saison = $letzteSaison; $saison >= $ersteSaison; $saison--) {
       $link = $turnier . '-' . substr($saison, 2) . substr($saison + 1, 2);
       $label = 'Saison ' . $saison . '/' . substr($saison + 1, 2);
       $result[$link] = $label;
    }
    return $result;
  }

?>
