<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Api\Request\UpdateTeamRecipientsRequest;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Team;
use Nsv\League\Entity\TeamRecipient;

class TeamServiceTest extends AbstractApiTest
{
  private TeamService $service;
  private League $league;
  private Division $division;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(TeamService::class);
    $this->league = $this->leagueRepository->findByPathOrPrefix('nsv-2526');
    $this->division = $this->league->divisions[0];
  }

  public function testTeam1() {
    $team = $this->division->teams()[0];
    $model = $this->service->team($team);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testTeam1_withSubstitute() {
    $this->league->configSubstituteTeams = 1;
    $team = $this->division->teams()[0];
    $model = $this->service->team($team);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testTeam2() {
    $team = $this->division->teams()[1];
    $model = $this->service->team($team);
    $this->assertModel($model, __FILE__, __FUNCTION__);
  }

  public function testUpdateRecipients() {
    $this->changeAndVerifyRecipients([], ['a@example.com', 'b@example.com']);
    $this->changeAndVerifyRecipients(['a@example.com', 'b@example.com'], ['a@example.com', 'c@example.com']);
    $this->changeAndVerifyRecipients(['a@example.com', 'c@example.com'], []);
  }

  private function changeAndVerifyRecipients(array $before, array $after) {
    $team = $this->division->teams()[0];
    $this->assertEquals($before, $this->recipients($team));

    $request = new UpdateTeamRecipientsRequest();
    $request->recipients = $after;
    $this->service->updateRecipients($team, $request);

    $this->clear();
    $team = $this->division->teams()[0];
    $this->assertEquals($after, $this->recipients($team));

    $this->clear();
  }

  private function recipients(Team $team) {
    return array_map(function (TeamRecipient $recipient) {
      return $recipient->mail;
    }, \iterator_to_array($team->additionalRecipients));
  }
}
