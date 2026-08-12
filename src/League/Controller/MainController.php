<?php

namespace Nsv\League\Controller;

use Nsv\Dwz\DsbDatabase;
use Nsv\League\Api\Model\Player;
use Nsv\League\Api\Service\PlayerService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Api\Service\TeamService;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\TokenAuth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the main publicly accessible routes.
 */
// TODO: Maybe rename to LeagueController?
#[Route('/ligen/{league}/', name: 'league_')]
class MainController extends AbstractLeagueController {

  const HOME_NEXT_DATES_COUNT = 2;
  const HOME_MAX_DATES_COUNT = 4;

  #[Route('overview/', name: 'overview')]
  public function overview(
    #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\d{4}-\d{2}-\d{2}$/'])]
    ?string $date,
    ScheduleService $service
  ): Response {
    $allDates = $service->leagueDates($this->league);
    if (!count($allDates)) {
      $this->addInfoMessage('Noch keine Spieltage hinterlegt.');
      return $this->renderWithLegacySystem('overview-no-dates.html.twig');
    }

    $today = date('Y-m-d');
    $dateToShow = $date ?: $service->closestDate($allDates, $today);
    $matches = $service->matchesByDate($this->league, $dateToShow);  // TODO: ...ForDate

    $hasMatches = false;
    foreach ($matches as $division) {
      if ($division->hasPairings()) {
        $hasMatches = true;
        break;
      }
    }
    
    return $this->renderWithLegacySystem('overview.html.twig', [
      'tabs' => $allDates,
      'activeTab' => $dateToShow,
      'matches' => $matches,
      'hasMatches' => $hasMatches,
      'isHomescreen' => true
    ]);
  }

  #[Route('m/{teamId}/', name: 'team')]
  public function team(TeamService $service, ScheduleService $scheduleService, int $teamId, TokenAuth $tokenAuth): Response {
    $teamEntity = $this->league->teamById($teamId);
    $allowEdit = $tokenAuth->mayEditTeam($teamEntity);
    $allowPlayerEdit = $this->auth->isDivisionManager($teamEntity->division);
    $team = $service->team($teamEntity, $allowEdit);

    $playerDialogParams = null;
    $editPlayerDialogParams = [];
    $deletePlayerDialogParams = [];
    $reorderPlayersDialogParams = null;
    if ($allowPlayerEdit) {
      // A team's ZPS can contain multiple club numbers concatenated if it's a union of clubs.
      // Just use the first club for DWZ search suggestions.
      $preferredZps = $teamEntity->zps ? substr($teamEntity->zps, 0, DsbDatabase::ZPS_CLUB_LENGTH) : null;

      // Preselect the current round for late registration, unless the season hasn't started yet.
      // A team may not have a division assigned (yet) - closestRound() requires one, and
      // config('rounds') falls back to the league-wide setting when there's no division.
      $closestRound = $teamEntity->division ? $scheduleService->closestRound($teamEntity->division, date('Y-m-d')) : null;
      $currentRound = ($closestRound && $closestRound->round > 1) ? $closestRound->round : null;
      $roundCount = $teamEntity->division ? $teamEntity->division->config('rounds') : $this->league->configRounds;

      $playerDialogParams = json_encode(Encoding::deep_utf8_encode([
        'teamId' => $teamId,
        'roundCount' => $roundCount,
        'preferredZps' => $preferredZps,
        'currentRound' => $currentRound
      ]));

      // Lookup for the games already computed by TeamService::team() above, keyed by player id,
      // so we don't need to re-query games per player just for the delete-eligibility check.
      $ownPlayerModelsById = [];
      foreach ($team->playersByTeamNumber[$teamEntity->number] ?? [] as $playerModel) {
        $ownPlayerModelsById[$playerModel->id] = $playerModel;
      }

      // Recreating the dialog params for each player with YOB and correct encoding.
      foreach ($teamEntity->players as $playerEntity) {
        $dialogPlayer = Player::fromEntity($playerEntity);
        $dialogPlayer->yearOfBirth = $playerEntity->yearOfBirth();
        $editPlayerDialogParams[$playerEntity->id] = json_encode(Encoding::deep_utf8_encode([
          'teamId' => $teamId,
          'roundCount' => $roundCount,
          'preferredZps' => $preferredZps,
          'currentRound' => null,
          'player' => $dialogPlayer
        ]));

        $isLateRegistered = $playerEntity->lateRegistrationRound !== null;
        $hasGames = !empty($ownPlayerModelsById[$playerEntity->id]->games);
        $canDelete = ($isLateRegistered || $currentRound === null) && !$hasGames;
        if ($canDelete) {
          $deletePlayerDialogParams[$playerEntity->id] = json_encode(Encoding::deep_utf8_encode([
            'teamId' => $teamId,
            'playerId' => $playerEntity->id,
            'playerName' => $playerEntity->name(),
          ]));
        }
      }

      $reorderPlayersDialogParams = json_encode(Encoding::deep_utf8_encode([
        'teamNumber' => $teamEntity->number,
        'firstPlayerNumber' => $teamEntity->firstPlayerNumber(),
        // TODO: Remove deep copy once everything is in UTF8.
        'team' => unserialize(serialize($team)),
      ]));
    }

    return $this->renderWithLegacySystem('team.html.twig', [
      'team' => $team,
      'teamEntity' => $teamEntity,
      'allowEdit' => $allowEdit,
      'allowPlayerEdit' => $allowPlayerEdit,
      'showContactInfo' =>  $this->league->year >= date('Y') - 1 || $allowEdit,
      'updateNameDialogParams' => json_encode(Encoding::deep_utf8_encode([
        'id' => $teamId,
        'name' => $teamEntity->name,
        'number' => $teamEntity->number
      ])),
      'playerDialogParams' => $playerDialogParams,
      'editPlayerDialogParams' => $editPlayerDialogParams,
      'deletePlayerDialogParams' => $deletePlayerDialogParams,
      'reorderPlayersDialogParams' => $reorderPlayersDialogParams
    ]);
  }

  #[Route('api/teams/{teamId}/', name: 'api_team')]
  public function team_api(TeamService $service, int $teamId, TokenAuth $tokenAuth): Response {
    $teamEntity = $this->league->teamById($teamId);
    $allowEdit = $tokenAuth->mayEditTeam($teamEntity);
    $team = $service->team($teamEntity, $allowEdit);
    if (!$allowEdit) {
      $team->captain->mail = '** REDACTED **';
      $team->captain->phone = '** REDACTED **';
      $team->captain->phone2 = '** REDACTED **';
    }
    return $this->apiResponse($team);
  }

  #[Route('s/{playerId}/', name: 'player')]
  public function player(PlayerService $service, int $playerId): Response {
    $player = $service->player($this->league, $playerId);
    return $this->renderWithLegacySystem('player.html.twig', ['player' => $player]);
  }

  #[Route('s/{playerId}/debug/', name: 'player_debug')]
  public function player_debug(PlayerService $service, int $playerId): Response {
    $player = $service->player($this->league, $playerId);
    return $this->debugResponse($player);
  }
}
 