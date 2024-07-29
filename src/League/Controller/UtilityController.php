<?php

namespace Nsv\League\Controller;


use Nsv\League\Api\Service\MatchDayService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Core\LegacySystem;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Round;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Nsv\League\Api\Service\StatisticsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Controller for utility routes, only used for development purposes.
 */
#[Route('/ligen/{league}/', name: 'utility_', priority: -100)]
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


  /**
   * Output the SQL Queries for a response. Use the X-Debug-Token of Symfonys profiler.
   */
  #[Route('profiler-queries/{division}/{token}', name: 'profiler-queries', methods: ['GET'], priority: 1000)]
  public function profilerQueries(Request $request, string $token): Response {
    $profile = $this->profiler->loadProfile($token);
    // Get the queries from the DB Collector.
    $queries = $profile->getCollector('db')->getQueries();

    return $this->render('utility/profiler-queries.html.twig', ['queries' => $queries['league']]);
  }


}