<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\ScheduleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the main publicly accessible routes.
 * 
 * TODO: Merge with Division and Team controller
 */
#[Route('/ligen/{league}/', name: 'league_')]
class MainController extends AbstractLeagueController {

  #[Route('overview/unstable-api/', name: 'overview_api')]
  public function overview_api(ScheduleService $service): Response {
    // TODO: Check if all matches with games from the same day.
    $overview = $service->leagueOverview($this->league, '2023-02-04', false);
    return $this->apiResponse($overview);
  }
}
