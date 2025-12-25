<?php

/**
 * Exports are deprecated and will be removed in the future.
 * If there is a need for it in the future, I'd recommend to use the public JSON API.
 */
global $globals;
@$_GET['auth'] === substr($globals['masterpasswort'], 0, 8) or die('Bitte wenden Sie sich an webmaster@nsv-online.de für Exporte');

require_once ( "turnier.inc.php" );
require_once ( "mannschaft.class.php" );

if ( $_GET ["format"] == "u12spielplaner"){
  require_once("../_module/export/u12spielplaner.inc.php");
}
