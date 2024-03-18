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

  $bridge = SED_Bridge();
  $league = $bridge->league;

  // Felder aus Turnier-Tabelle in $prefs speichern
  global $globals;
  global $prefs;
  $globals['tid'] = $league->id;
  $prefs = SED_Query("SELECT t.* FROM turniere as t WHERE t.id=?", [$globals['tid']])->fetchAssociative();

  // Fehler?
  if ( !is_array ( $prefs ) )
    SED_Error ( "Das Turnier scheint nicht zu existieren!", true );

  // Template berechnen
  $globals ['templatedir'] = "$globals[basedir]/_templates/nsv2020";

  // Neu: basepath bei allen Links mit ausgeben.
  $globals ['basepath'] = "/ligen/$prefs[directory]";

  // Staffeln
  $globals ['staffeln'] = array ();
  foreach ($league->divisions as $division) {
    $globals['staffeln'][$division->id] = $division->name;
  }
  if ($bridge->division) {
    $_GET['staffel'] = $bridge->division->id;
  }
  
  // Mannschaften
  $globals ['teams'] = array ();
  foreach ($league->teams as $team) {
    $globals['teams'][$team->id] = $team->nameWithNumber();
  }

  // Liefert zu einem Spieltag den Timestamp
  function SED_GetTermin ( $runde, $staffel, $datumsformat = '%d.%m.%Y' )
  {
    global $globals;
    // Eigentlich gibt es mittlerweile ja viewStaffeltermine, allerdings funktioniert das hier auch, wenn keine Turniertermine festgelegt wurden, sondern nur Staffeltermine
    return SED_Query("
      SELECT DATE_FORMAT(te.datum,'$datumsformat') as datum
      FROM termine as te
      WHERE te.turnier=? and te.runde=? and (te.staffel is null or te.staffel=?)
      ORDER BY staffel DESC LIMIT 1",
      [
        $globals['tid'],
        $runde,
        $staffel
      ]
    )->fetchOne();
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
    return SED_Value("
      SELECT IF($feld IS NULL,".$prefs[$feld].",$feld) FROM staffeln WHERE id=?",
      [$staffel]
    );
  }

  // Liefert die letzte Runde in der eine Paarung gesetzt ist
  function SED_GetLetzteRunde ( $staffel )
  {
    return SED_Query("SELECT MAX( runde )  FROM paarungen WHERE staffel=?", [$staffel])->fetchOne();
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
      'test' => 2015,
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
