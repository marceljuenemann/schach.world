<?php

namespace Tests\League;

use Doctrine\ORM\EntityNotFoundException;
use Nsv\League\Api\Service\RankingService;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\League\LeagueTestCase;

class RankingTest extends LeagueTestCase {
  use MatchesSnapshots;
}