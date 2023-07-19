<?php

namespace Nsv\League\Api\Service;

use Nsv\Dwz\IsewaseDwzCalculator;
use PHPUnit\Framework\MockObject\MockObject;

class PlayerServiceTest extends AbstractApiTest
{
  private MockObject $dwzService;
  private PlayerService $service;

  protected function setUp(): void {
    parent::setUp();
    $this->dwzService = $this->createMock(IsewaseDwzCalculator::class);
    $this->container->set(IsewaseDwzCalculator::class, $this->dwzService);
    $this->service = $this->container->get(PlayerService::class);
  }

  public function testPlayer1() {
    $player = $this->division->teams()[0]->players[0];
/*
    $newsRepository->expects(self::once())
        ->method('findNewsFromLastMonth')
        ->willReturn([
            new News('some news'),
            new News('some other news'),
        ])
    ;
*/

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
