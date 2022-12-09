<?php
/**
 * Glue code for making the new NSV 2020 design work with the legacy template  
 * system.
 */

function nsv2020_url() {
  return '/core/nsv2020';
}

function nsv2020_head() {
  global $globals, $prefs;
  echo "<script src='" . nsv2020_url() . "/vendor/jquery-3.3.1.slim.min.js'></script>";
  echo "<link rel='Stylesheet' href='$globals[templatedir]/ergebnisdienst.css'>";
  echo "<script src='$globals[templatedir]/ergebnisdienst.js'></script>";
  echo "<title>$prefs[name]</title>";
  if (isset($globals['premod_headtag'])) {
    echo $globals ['premod_headtag'];
  }  
}

function nsv2020_is_utf8() {
  return FALSE;
}

function nsv2020_navbar() {
  global $globals, $prefs;
  if ($prefs['template'] == 'nsv') {
    require_once("$globals[basedir]/../mainmenu.menu.php");
    return $nsvMainmenu;
  }
  if ($prefs['template'] == 'bezirk1') {
    require_once("$globals[basedir]/../core/nsv2020/bezirk1/navbar.php");
    return nsv2020_bezirk1_navbar();
  }
  
  $menu = array("&Uuml;bersicht" => array("?"));
  foreach ( $globals ["staffeln"] as $id => $name ) {
    // Namen ggf. kürzen verarbeiten
    if ( count ( $globals ["staffeln"] ) > 3 ) {
      $name = str_replace ( "Staffel ", "", str_replace ( "Gruppe ", "", $name ) );
    }
    $menu[$name] = array("?staffel=$id&r=");
  }
  return $menu;
}

function nsv2020_theme() {
  global $prefs;
  if ($prefs['template'] == 'bezirk1') return 'bezirk1';
  if ($prefs['template'] == 'optimus') return 'ergebnisdienst';
  return 'nsv';
}

function nsv2020_custom_sitename() {
  global $prefs;
  return $prefs['name'];
}


include ("$globals[basedir]/../core/nsv2020/header.php");
echo "<div class='col-12 col-lg-9 order-lg-2'><div class='card shadow nsv-card'><div class='card-body'>";

if (strlen($prefs['infomeldung']) > 2) {
  echo "<div class='sed_infomeldung'>$prefs[infomeldung]</div>";
  echo "</div></div><div class='card shadow nsv-card'><div class='card-body'>";
}
