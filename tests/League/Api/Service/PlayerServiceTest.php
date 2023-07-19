<?php

namespace Nsv\League\Api\Service;

use Nsv\Dwz\IsewaseDwzCalculator;
use Nsv\League\Repository\GameRepository;
use Nsv\League\Repository\PlayerRepository;

class PlayerServiceTest extends AbstractApiTest
{
  /**
   * Test cases: 
   * - Games:
   *   - Different boards
   *   - Different rounds
   *   - All the different results  win, loss, remis, bye
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

  public function testPlayer1() {
    $player = $this->division->teams()[0]->players[0];
    $model = $this->service->player($this->league, $player->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testPlayer2() {
    $player = $this->division->teams()[1]->players[0];
    $model = $this->service->player($this->league, $player->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testPlayer3_withoutGamesAndRating() {
    $player = $this->division->teams()[0]->players[1];
    $model = $this->service->player($this->league, $player->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }
}
