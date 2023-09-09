<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the Division entity based on the current Request, which must be matched
 * by a route with a {division} path parameter.
 */
class DivisionProvider
{
  function __construct(
    private League $league,
    private RequestStack $requestStack
  ) {}

  public function __invoke(): Division {
    $request = $this->requestStack->getCurrentRequest();
    $divisionPath = $request->attributes->get('division');
    return $this->league->divisionByPath($divisionPath);
  }
}
