<?php

namespace Nsv\League\Controller;

use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}/{division}/', name: 'league_')]
class DivisionController extends AbstractLeagueController {

  /**
   * Main entry point for the League Manager. Exact action is determined by query parameters.
   */
  #[Route('spielplan/', name: 'schedule')]
  public function schedule(): Response {
    return $this->renderWithLegacySystem('schedule.html.twig');
  }
}
