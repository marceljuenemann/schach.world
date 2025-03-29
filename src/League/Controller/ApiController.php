<?php

namespace Nsv\League\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Request\CreateDivisionRequest;
use Nsv\League\Api\Request\DivisionOrderRequest;
use Nsv\League\Api\Request\UpdateTeamCaptainRequest;
use Nsv\League\Api\Request\UpdateTeamNameAndNumberRequest;
use Nsv\League\Api\Request\UpdateTeamRecipientsRequest;
use Nsv\League\Api\Request\UpdateTeamVenueRequest;
use Nsv\League\Api\Service\DivisionService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Api\Service\TeamService;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Core\LegacySystem;
use Nsv\League\Core\TokenAuth;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Team;
use Nsv\League\Repository\CacheRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}/api/', name: 'league_api_')]
class ApiController extends AbstractLeagueController {

  function __construct(
    League                         $league,
    LeagueAuthState                $auth,
    LegacySystem                   $legacySystem,
    private EntityManagerInterface $leagueEntityManager,
    private CacheRepository        $cacheRepository
  ) {
    parent::__construct($league, $auth, $legacySystem);
  }

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
      $model->matchDays = $scheduleService->divisionSchedule($division);
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

  // TODO: Maybe move everything to a TeamController instead of having pretty generic "ApiController" and "MainController"
  #[Route('teams/{id}/updateNameAndNumber/', methods: ['PUT'], name: 'team_name_and_number_update')]
  public function updateTeamNameAndNumber(Team $team, #[MapRequestPayload] UpdateTeamNameAndNumberRequest $request): Response {
    $this->auth->requireDivisionManager($team->division);
    Encoding::deep_utf8_decode($request);

    $team->name = $request->name;
    $team->number = $request->number;

    $this->leagueEntityManager->persist($team);
    $this->leagueEntityManager->flush();
    $this->cacheRepository->clearCache($this->league);

    return $this->apiResponse();
  }  

  #[Route('teams/{id}/venue/', methods: ['PUT'], name: 'team_venue_update')]
  public function updateTeamVenue(Team $team, #[MapRequestPayload] UpdateTeamVenueRequest $request, TeamService $service, TokenAuth $auth): Response {
    $auth->requireTeamManager($team);
    Encoding::deep_utf8_decode($request);
    $service->updateVenue($team, $request);
    return $this->apiResponse();
  }

  #[Route('teams/{id}/captain/', methods: ['PUT'], name: 'team_captain_update')]
  public function updateTeamCaptain(Team $team, #[MapRequestPayload] UpdateTeamCaptainRequest $request, TeamService $service, TokenAuth $auth): Response {
    $auth->requireTeamManager($team);
    Encoding::deep_utf8_decode($request);
    $service->updateCaptain($team, $request);
    return $this->apiResponse();
  }

  #[Route('teams/{id}/recipients/', methods: ['PUT'], name: 'team_recipients_update')]
  public function updateTeamRecipients(Team $team, #[MapRequestPayload] UpdateTeamRecipientsRequest $request, TeamService $service, TokenAuth $auth): Response {
    $auth->requireTeamManager($team);
    Encoding::deep_utf8_decode($request);
    $service->updateRecipients($team, $request);
    return $this->apiResponse();
  }
}
