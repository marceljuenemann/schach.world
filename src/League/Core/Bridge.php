<?php

namespace Nsv\League\Core;

use Nsv\WebApp\Repository\EventRepository;
use Nsv\League\Repository\LeagueRepository;

/**
 * Bridge between the Symfony world and the legacy ligen/ code.
 */
class Bridge {

  function __construct(
      public EventRepository $eventRepository,
      public LeagueRepository $leagues) {
  }
}
