<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\WebApp\Core\WordPress\Auth;

/**
 * Integration with the legacy league system located in the public/ligen/ directory.
 */
class LegacySystem
{
  function __construct() {}

  /**
   * Sets up the database connection and global variables of the legacy system without
   * processing the request or outputting anything.
   * 
   * TODO: Ensure this is the only entry point into the legacy system.
   */
  function initialize(League|null $league = null, Division|null $division = null) {
    if (Auth::isAdmin()) {
      $_GET['debugme'] = 1;
    }

    chdir(ABSPATH . '../ligen/_inc');
    global $globals;
    $globals['basedir'] = '..';

    if (isset($league)) {
      $globals['league'] = $league;
      $globals['tid'] = $league->id;
      if (isset($division)) {
        $globals['division'] = $division;
        $_GET['staffel'] = $division->id;
      }
    }

    require_once ( "main.inc.php" );
    require_once ( "connect.inc.php" );

    // Don't send Content-Type header: https://www.saotn.org/php-56-default_charset-change-may-break-html-output/
    ini_set( 'default_charset', "" );
  }
}
