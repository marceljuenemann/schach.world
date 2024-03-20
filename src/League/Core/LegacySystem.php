<?php

namespace Nsv\League\Core;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\WebApp\Core\WordPress\Auth;
use Psr\Log\LoggerInterface;

/**
 * Integration with the legacy league system located in the public/ligen/ directory.
 */
class LegacySystem
{
  function __construct(
    private string $projectDir,
    // Pubic variables exposed to the legacy system.
    public readonly EntityManagerInterface $leagueEntityManager,
    public readonly LoggerInterface $leagueLogger
  ) {}

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

    chdir($this->projectDir . '/public/ligen/_inc');
    global $globals;
    $globals['basedir'] = '..';
    $globals['bridge'] = $this;

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

  /**
   * Invokes an _admin script and returns the output.
   */
  function invokeAdminScript(string $scriptName) {
    global $globals;
    $globals['adminScript'] = $scriptName;
    ob_start();
    include("../_admin/{$scriptName}.php");
    return ob_get_clean();
  }
}
