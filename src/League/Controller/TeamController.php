<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\PlayerService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}/', name: 'league_')]
class TeamController extends AbstractLeagueController {

  /*
  #[Route('spielplan/', name: 'schedule')]
  public function schedule(ScheduleService $service): Response {
    $matchDays = $service->matchDays($this->division);
    return $this->renderWithLegacySystem('schedule.html.twig', ['matchDays' => $matchDays]);
  }
  */

  // TODO: consider moving to an API controller?
  #[Route('s/{playerId}/unstable-api/', name: 'player_api')]
  public function player_api(PlayerService $service, int $playerId): Response {
    $player = $service->player($this->league, $playerId);
    return $this->apiResponse($player);
  }
}
