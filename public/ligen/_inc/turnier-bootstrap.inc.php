<?php
/**
 * Initializes global variables for the current league.
 * 
 * This was extracted from turnier.inc.php to allow reinitialization in tests.
 */

  $bridge = SED_Bridge();
  $league = $bridge->league;

  // Felder aus Turnier-Tabelle in $prefs speichern
  global $globals;
  global $prefs;
  $globals['tid'] = $league->id;
  $prefs = SED_Query('SELECT t.* FROM turniere as t WHERE t.id=?', [$globals['tid']])->fetchAssociative();

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
