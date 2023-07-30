<?php

namespace Nsv\League\Api\Service;

class TeamServiceTest extends AbstractApiTest
{
  private TeamService $service;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(TeamService::class);
  }

  public function testTeam1() {
    $team = $this->division->teams()[0];
    $model = $this->service->team($this->league, $team->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testTeam1_withSubstitute() {
    $this->league->configSubstituteTeams = 1;
    $team = $this->division->teams()[0];
    $model = $this->service->team($this->league, $team->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testTeam2() {
    $team = $this->division->teams()[1];
    $model = $this->service->team($this->league, $team->id);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }
}
