<?php

namespace Nsv\League\Api\Service;

use Nsv\Dwz\IsewaseDwzCalculator;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Team;
use Tests\League\LeagueTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class PlayerServiceTest extends LeagueTestCase
{
  private MockObject $dwzService;
  private PlayerService $service;
  private League $league;
  private Team $team;

  protected function setUp(): void {
    parent::setUp();
    $this->dwzService = $this->createMock(IsewaseDwzCalculator::class);
    $this->container->set(IsewaseDwzCalculator::class, $this->dwzService);
    $this->service = $this->container->get(PlayerService::class);
    $this->league = $this->leagueRepository->findByPathOrPrefix('nsj-2526');
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
    $this->assertMatchesSnapshot($model);
  }

  public function testPlayer2() {
    $player = $this->team->players[3];
    $this->dwzService->expects(self::once())
        ->method('calculate')
        ->will($this->returnCallback(function() {
          return func_get_args();
        }));

    $model = $this->service->player($this->league, $player->id);
    $this->assertMatchesSnapshot($model);
  }

  public function testPlayer3_withoutGamesAndRating() {
    $player = $this->team->players[0];
    $this->dwzService->expects(self::never())->method('calculate');
    $model = $this->service->player($this->league, $player->id);
    $this->assertMatchesSnapshot($model);
  }
}
