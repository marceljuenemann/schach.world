<?php

namespace Nsv\League\Core;

use Exception;
use Nsv\League\Entity\League;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides the League entity based on the current Request, which must be matched
 * by a route with a {league} path parameter. Any service requiring the current
 * League can simply inject the League object.
 */
class LeagueProvider
{
  function __construct(
    private RequestStack $requestStack,
    private LeagueRepository $leagueRepository
  ) {}

  public function __invoke(): League {
    $request = $this->requestStack->getCurrentRequest();

    $leagueName = $request->attributes->get('league');
    if (!$leagueName) {
      throw new Exception("League can only be injected for routes with a {league} path parameter");
    }

    $league = $this->leagueRepository->findByPath($leagueName);
    if (!$league) {
      throw new NotFoundHttpException("League not found");
    }

    // Optimization: Fetch all divisions and teams.
    $league->divisions->toArray();
    $league->teams->toArray();

    return $league;
  }
}
