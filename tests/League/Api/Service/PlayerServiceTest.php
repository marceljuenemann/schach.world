<?php

namespace Nsv\League\Api\Service;

use Nsv\Dwz\IsewaseDwzCalculator;
use Nsv\League\Repository\GameRepository;
use Nsv\League\Repository\PlayerRepository;

class PlayerServiceTest extends AbstractApiTest
{
  /**
   * Test cases: 
   * Model- with ZPS
   * - without ZPS
   * - Games:
   *   - Different boards
   *   - Different rounds
   *   - All the different results  win, loss, remis, bye
   * - DWZ: should just be mocked really, too complex otherwise
   */

  private PlayerService $service;

  protected function setUp(): void {
    parent::setUp();
    $this->service = new PlayerService(
      $this->container->get(PlayerRepository::class),
      $this->container->get(GameRepository::class),
      new IsewaseDwzCalculator(function($params) {
        return $params;
      })
    );
  }

  public function testPlayer_withRatingAndTitle() {
    $player = $this->division->teams()[0]->players[0];
    $model = $this->service->player($this->league, $player->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }
}
