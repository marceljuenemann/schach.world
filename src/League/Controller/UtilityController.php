<?php

namespace Nsv\League\Controller;


use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Core\LegacySystem;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for utility routes, only used for development purposes.
 */
#[Route('/ligen/', name: 'utility_', priority: -100)]
class UtilityController extends AbstractLeagueController {

  private $profiler;

  public function __construct(League                         $league,
                              LeagueAuthState                $auth,
                              LegacySystem                   $legacySystem,
                              Division                       $division,
                              Profiler $profiler) {
    parent::__construct($league, $auth, $legacySystem);
    $this->division = $division;
    $this->profiler = $profiler;
  }


  #[Route('{division}/profiler-queries', name: 'profiler-queries', methods: ['GET'])]
  public function profilerQueries() {
    return $this->renderWithLegacySystem('utility/profiler-queries.html.twig', []);
  }

}