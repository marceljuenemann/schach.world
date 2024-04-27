<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\Team;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides team leaders with the ability to authenticate with simple tokens instead of
 * having to login. 
 */
class TokenAuth
{
  private Request $request;

  function __construct(
    private LeagueAuthState $auth,
    private LegacySystem $legacySystem,
    RequestStack $requestStack
  ) {
    $this->request = $requestStack->getCurrentRequest();
  }

  /**
   * Checks whether the request has a valid token for the given team, or whether the
   * user is logged in with appropriate rights to edit this team's data.
   */
  function mayEditTeam(Team $team): bool {
    return $this->auth->isDivisionManager($team->division) || $this->hasToken(function () use ($team) {
      return $this->teamToken($team);
    });
  }

  private function hasToken(callable $tokenProvider): bool {
    if ($token = $this->getRequestToken()) {
      return $token === call_user_func($tokenProvider);
    }
    return false;
  }

  private function getRequestToken(): string|null {
    if ($this->request->query->has('auth')) {
      return $this->request->query->get('auth');
    } else if ($this->request->headers->has('X-Auth')) {
      return $this->request->headers->get('X-Auth');
    }
    return null;
  }

  private function teamToken(Team $team): string {
    return $this->generateLegacyToken('MID' . $team->id);
  }

  private function generateLegacyToken(string $str) {
    $this->legacySystem->initialize();
    global $globals;
    return substr(md5("$str$globals[salt]"), 0, $globals['md5-length']); 
  }
}
