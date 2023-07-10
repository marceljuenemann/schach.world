<?php

namespace Nsv\League\Core;

use Nsv\League\Repository\LeagueRepository;

/**
 * Bridge between the Symfony world and the legacy ligen/ code.
 */
class Bridge {

  function __construct(
      public LeagueRepository $leagues) {
  }
}
