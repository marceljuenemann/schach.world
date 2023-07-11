<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\League;
use Nsv\League\Repository\LeagueRepository;

/**
 * Bridge between the Symfony world and the legacy ligen/ code.
 */
class Bridge {

  /**
   * The League for this request.
   */
  public ?League $league;

  function __construct(
      public LeagueRepository $leagues) {
  }
}
