<?php

namespace Nsv\League\Controller;

use Nsv\League\Entity\League;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Abstract controller for a specific league, which is specified through the league slug
 * in the URL path.
 */
class AbstractLeagueController extends AbstractController {

  protected League $league;

  public function setLeague($league) {
    $this->league = $league;
  }
}
