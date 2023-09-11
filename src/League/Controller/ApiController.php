<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Request\CreateDivisionRequest;
use Nsv\League\Api\Request\DivisionOrderRequest;
use Nsv\League\Api\Request\UpdateTeamVenueRequest;
use Nsv\League\Api\Service\DivisionService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Api\Service\TeamService;
use Nsv\League\Core\Encoding;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}/api/', name: 'league_api_')]
class ApiController extends AbstractLeagueController {

  /**
   * *Unstable* API for displaying a pairing list in the admin area.
   * 
   * TODO: Remove the pairing list and instead let users enter results directly
   * via the match day or overview routes. 
   */
  #[Route('unstable/pairings/', name: 'pairings')]
  public function pairings(ScheduleService $scheduleService): Response {
    $this->auth->requireDivisionManager();
    $today = date('Y-m-d');
    $divisions = [];
    foreach ($this->league->divisions as $division) {
      $model = Division::fromEntity($division);
      $model->matchDays = $scheduleService->matchDays($division);
      $model->closestDate = $scheduleService->closestDate(array_map(function ($date) {
        return $date->date;  // TODO: make this cleaner
      }, $division->dates()), $today);
      $divisions[] = $model;
    }
    return $this->apiResponse($divisions);
  }

  #[Route('divisions/create/', methods: ['POST'], name: 'division_create')]
  public function createDivision(#[MapRequestPayload] CreateDivisionRequest $request, DivisionService $service): Response {
    $this->auth->requireLeagueManager();
    Encoding::deep_utf8_decode($request);
    $service->createDivision($this->league, $request);
    return $this->apiResponse();
  }

  #[Route('divisions/order/', methods: ['PUT'], name: 'division_order')]
  public function reorderDivisions(#[MapRequestPayload] DivisionOrderRequest $request, DivisionService $service): Response {
    $this->auth->requireLeagueManager();
    $service->updateOrder($this->league, $request);
    return $this->apiResponse();
  }

  #[Route('teams/{teamId}/venue/', methods: ['PUT'], name: 'team_venue_update')]
  public function updateTeamVenue(int $teamId, #[MapRequestPayload] UpdateTeamVenueRequest $request, TeamService $service): Response {
    $team = $this->league->teamById($teamId);
    $this->auth->requireDivisionManager($team->division);
    Encoding::deep_utf8_decode($request);
    $service->updateVenue($team, $request);
    return $this->apiResponse();
  }
}
