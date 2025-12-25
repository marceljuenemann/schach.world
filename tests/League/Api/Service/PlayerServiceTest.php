<?php

namespace Nsv\League\Api\Service;

use Nsv\Dwz\IsewaseDwzCalculator;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Team;
use PHPUnit\Framework\MockObject\MockObject;

class PlayerServiceTest extends AbstractApiTest
{
  private MockObject $dwzService;
  private PlayerService $service;
  private League $league;
  private Division $division;
  private Team $team;

  protected function setUp(): void {
    parent::setUp();
    $this->dwzService = $this->createMock(IsewaseDwzCalculator::class);
    $this->container->set(IsewaseDwzCalculator::class, $this->dwzService);
    $this->service = $this->container->get(PlayerService::class);
    $this->league = $this->leagueRepository->findByPathOrPrefix('nsj-2526');
    $this->division = $this->league->divisionByPath('jugendliga-niedersachsen');
    $this->team = $this->league->teamById(8412);  // SK Lehrte
  }

  public function testPlayer1() {
    $player = $this->team->players[2];
    $this->dwzService->expects(self::once())
        ->method('calculate')
        ->will($this->returnCallback(function() {
          return func_get_args();
        }));

    $model = $this->service->player($this->league, $player->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testPlayer2() {
    $player = $this->team->players[1];
    $this->dwzService->expects(self::once())
        ->method('calculate')
        ->will($this->returnCallback(function() {
          return func_get_args();
        }));

    $model = $this->service->player($this->league, $player->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testPlayer3_withoutGamesAndRating() {
    $player = $this->team->players[0];
    $this->dwzService->expects(self::never())->method('calculate');
    $model = $this->service->player($this->league, $player->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }
}
