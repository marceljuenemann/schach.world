<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\PlayerService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// TODO: Maybe just merge with DivisionController into MainController?
// Or FrontendController? Or PublicController? Not much logic in here anyways.
#[Route('/ligen/{league}/', name: 'league_')]
class TeamController extends AbstractLeagueController {

  #[Route('s/{playerId}/', name: 'player')]
  public function schedule(PlayerService $service, int $playerId): Response {
    $player = $service->player($this->league, $playerId);
    return $this->renderWithLegacySystem('player.html.twig', ['player' => $player]);
  }

  // TODO: consider moving to an API controller?
  #[Route('s/{playerId}/unstable-api/', name: 'player_api')]
  public function player_api(PlayerService $service, int $playerId): Response {
    $player = $service->player($this->league, $playerId);
    return $this->apiResponse($player);
  }
}
