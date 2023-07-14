<?php

namespace Nsv\League\Controller;

use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}', name: 'league_')]
class DivisionController extends AbstractLeagueController {

  /**
   * Main entry point for the League Manager. Exact action is determined by query parameters.
   */
  #[Route('/{divisionPath}/spielplan/', name: 'schedule')]
  public function league(string $divisionPath): Response {
    $division = $this->league->divisionByPath($divisionPath);
    echo $division->name;

    // 2. Outsource to event listener
    // 3. Start twig
    // 4. Nice twig.

    
    return new Response("Hello World");
  }
}
