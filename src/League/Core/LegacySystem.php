<?php

namespace Nsv\League\Core;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Psr\Log\LoggerInterface;

/**
 * Integration with the legacy league system located in the public/ligen/ directory.
 */
class LegacySystem
{
  // Global variables for the legacy system.
  public League|null $league = null;
  public Division|null $division = null;

  private bool $intialized = false;

  function __construct(
    private string $projectDir,
    // Pubic variables exposed to the legacy system.
    public readonly EntityManagerInterface $leagueEntityManager,
    public readonly LoggerInterface $leagueLogger
  ) {}

  /**
   * Sets up the database connection and global variables of the legacy system without
   * processing the request or outputting anything.
   */
  function initialize() {
    if ($this->intialized) return;
    $this->intialized = true;

    chdir($this->projectDir . '/public/ligen/_inc');
    global $globals;
    $globals['basedir'] = '..';
    $globals['bridge'] = $this;

    require_once ( "../config.inc.php" );
    require_once ( "main.inc.php" );

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
