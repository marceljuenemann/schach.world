<?php

namespace Nsv\League\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Request\CreateDivisionRequest;
use Nsv\League\Api\Request\CreateOrUpdatePlayerRequest;
use Nsv\League\Api\Request\DivisionOrderRequest;
use Nsv\League\Api\Request\ReorderPlayersRequest;
use Nsv\League\Api\Request\UpdatePairingRequest;
use Nsv\League\Api\Request\UpdateTeamCaptainRequest;
use Nsv\League\Api\Request\UpdateTeamNameAndNumberRequest;
use Nsv\League\Api\Request\UpdateTeamRecipientsRequest;
use Nsv\League\Api\Request\UpdateTeamVenueRequest;
use Nsv\League\Api\Service\DivisionService;
use Nsv\League\Api\Service\PairingService;
use Nsv\League\Api\Service\PlayerService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Api\Service\TeamService;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Core\LegacySystem;
use Nsv\League\Core\TokenAuth;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Player;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}/api/', name: 'league_api_', requirements: ['id' => '\d+'])]
class ApiController extends AbstractLeagueController {

  function __construct(
    League                         $league,
    LeagueAuthState                $auth,
    LegacySystem                   $legacySystem,
    private EntityManagerInterface $leagueEntityManager
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

  #[Route('pairings/{id}/', methods: ['PUT'], name: 'pairing_update')]
  public function updatePairing(Pairing $pairing, #[MapRequestPayload] UpdatePairingRequest $request, PairingService $service): Response {
    // A league manager passes the check below for divisions of any league.
    if ($pairing->division->league->id !== $this->league->id) {
      throw new NotFoundHttpException('Pairing not found in this league');
    }
    $this->auth->requireDivisionManager($pairing->division);
    Encoding::deep_utf8_decode($request);
    $service->updatePairing($pairing, $request);
    return $this->apiResponse();
  }

  // TODO: Maybe move everything to a TeamController instead of having pretty generic "ApiController" and "MainController"
  #[Route('teams/{id}/updateNameAndNumber/', methods: ['PUT'], name: 'team_name_and_number_update')]
  public function updateTeamNameAndNumber(int $id, #[MapRequestPayload] UpdateTeamNameAndNumberRequest $request): Response {
    $team = $this->league->teamById($id);
    $this->auth->requireDivisionManager($team->division);
    Encoding::deep_utf8_decode($request);

    $team->name = $request->name;
    $team->number = $request->number;

    $this->leagueEntityManager->persist($team);
    $this->leagueEntityManager->flush();

    return $this->apiResponse();
  }  

  #[Route('teams/{id}/venue/', methods: ['PUT'], name: 'team_venue_update')]
  public function updateTeamVenue(int $id, #[MapRequestPayload] UpdateTeamVenueRequest $request, TeamService $service, TokenAuth $auth): Response {
    $team = $this->league->teamById($id);
    $auth->requireTeamManager($team);
    Encoding::deep_utf8_decode($request);
    $service->updateVenue($team, $request);
    return $this->apiResponse();
  }

  #[Route('teams/{id}/captain/', methods: ['PUT'], name: 'team_captain_update')]
  public function updateTeamCaptain(int $id, #[MapRequestPayload] UpdateTeamCaptainRequest $request, TeamService $service, TokenAuth $auth): Response {
    $team = $this->league->teamById($id);
    $auth->requireTeamManager($team);
    Encoding::deep_utf8_decode($request);
    $service->updateCaptain($team, $request);
    return $this->apiResponse();
  }

  #[Route('teams/{id}/recipients/', methods: ['PUT'], name: 'team_recipients_update')]
  public function updateTeamRecipients(int $id, #[MapRequestPayload] UpdateTeamRecipientsRequest $request, TeamService $service, TokenAuth $auth): Response {
    $team = $this->league->teamById($id);
    $auth->requireTeamManager($team);
    Encoding::deep_utf8_decode($request);
    $service->updateRecipients($team, $request);
    return $this->apiResponse();
  }

  #[Route('teams/{id}/players/', methods: ['POST'], name: 'team_player_create')]
  public function createPlayer(int $id, #[MapRequestPayload] CreateOrUpdatePlayerRequest $request, PlayerService $service): Response {
    $team = $this->league->teamById($id);
    $this->auth->requireDivisionManager($team->division);
    Encoding::deep_utf8_decode($request);
    $service->createPlayer($team, $request);
    return $this->apiResponse();
  }

  #[Route('teams/{id}/players/{playerId}/', methods: ['PUT'], name: 'team_player_update', requirements: ['playerId' => '\d+'])]
  public function updatePlayer(int $id, #[MapEntity(id: 'playerId')] Player $player, #[MapRequestPayload] CreateOrUpdatePlayerRequest $request, PlayerService $service): Response {
    $team = $this->league->teamById($id);
    if ($player->team->id !== $team->id) {
      throw new NotFoundHttpException('Player not found on this team');
    }
    $this->auth->requireDivisionManager($team->division);
    Encoding::deep_utf8_decode($request);
    $service->updatePlayer($player, $request);
    return $this->apiResponse();
  }

  #[Route('teams/{id}/players/{playerId}/', methods: ['DELETE'], name: 'team_player_delete', requirements: ['playerId' => '\d+'])]
  public function deletePlayer(int $id, #[MapEntity(id: 'playerId')] Player $player, PlayerService $service): Response {
    $team = $this->league->teamById($id);
    if ($player->team->id !== $team->id) {
      throw new NotFoundHttpException('Player not found on this team');
    }
    $this->auth->requireDivisionManager($team->division);
    $service->deletePlayer($player);
    return $this->apiResponse();
  }

  #[Route('teams/{id}/players/reorder/', methods: ['PUT'], name: 'team_player_reorder')]
  public function reorderPlayers(int $id, #[MapRequestPayload] ReorderPlayersRequest $request, PlayerService $service): Response {
    $team = $this->league->teamById($id);
    $this->auth->requireDivisionManager($team->division);
    $service->reorderPlayers($team, $request->playerIds);
    return $this->apiResponse();
  }
}
