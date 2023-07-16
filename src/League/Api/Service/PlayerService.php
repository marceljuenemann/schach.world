<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity;

class PlayerService
{
  public function player(Entity\League $league, int $playerId): array {
    return ['hello world'];
  }
}
