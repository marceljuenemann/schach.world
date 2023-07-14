<?php

namespace Nsv\League\Controller;

use Nsv\League\Repository\LeagueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{leaguePath}/{divisionPath}', name: 'league_')]
class DivisionController extends AbstractController {

  /**
   * Main entry point for the League Manager. Exact action is determined by query parameters.
   */
  #[Route('/spielplan/', name: 'schedule')]
  public function league(
        string $leaguePath, 
        string $divisionPath,
        LeagueRepository $leagueRepository
    ): Response {

    $league = $leagueRepository->find(1);
    $division = $league->divisionByPath($divisionPath);
    echo $division->name;

    // 2. Outsource to event listener
    // 3. Start twig
    // 4. Nice twig.

    
    return new Response("Hello World");
  }
}
