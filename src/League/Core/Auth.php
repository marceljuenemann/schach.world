<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * User authorization and authentication.
 */
class Auth
{
  // Session keys for legacy user system.
  const SESSION_KEY_LOGIN = 'league_login_time';
  const SESSION_KEY_LEAGUE = 'league_login_league_id';
  const SESSION_KEY_DIVISION = 'league_login_division_id';
  const SESSION_DURATION = 60 * 60;  // 1 hour

  private Session $session;

  function __construct(private RequestStack $requestStack) {
    $this->session = $requestStack->getSession();
  }

  /**
   * Verifies that the current user is a manager for the given league.
   * 
   * @return Division|null the division for which the user is authorizes, or null
   *    if the user may manage all divisions.
   * @throws AccessDeniedHttpException if verification failed.
   */
  function checkManagerAccess(League $league): Division|null {
    $loginTime = $this->session->get(self::SESSION_KEY_LOGIN);
    if (!is_numeric($loginTime) || $loginTime < time() - self::SESSION_DURATION) {
      throw new AccessDeniedHttpException('Invalid session.');
    }
    if ($this->session->get(self::SESSION_KEY_LEAGUE) != $league->id) {
      throw new AccessDeniedHttpException('Incorrect league.');
    }

    $divisionId = $this->session->get(self::SESSION_KEY_DIVISION);
    return $divisionId ? $league->divisionById($divisionId) : null;
  }

  /**
   * Logs a user in using the legacy user system.
   * 
   * @param $league the League for which the login action is performed
   * @param $user the user to login, e.g. s-123 for division 123 or t-3 for league 3.
   * @param $password the unprocessed password.
   * @throws NotFoundHttpException if user ID is incorrect
   * @throws AccessDeniedHttpException if password is incorrect
   */
  function legacyLogin(League $league, string $user, string $password) {
    if ($user == 's') {
      $divisionId = (int) substr($user, 2);
      $user = $league->divisionById($divisionId)->manager;
    } else {
      $divisionId = 0;
      $user = $league->manager;
    }

    global $globals;  // TODO: move master password somewhere else
    $pw = md5($password);
    if (!$user || ($pw !== $user->password && $pw !== $globals['masterpasswort'])) {
      throw new AccessDeniedHttpException("Incorrect password");
    }

    $this->session->set(self::SESSION_KEY_LOGIN, time());
    $this->session->set(self::SESSION_KEY_LEAGUE, $league->id);
    $this->session->set(self::SESSION_KEY_DIVISION, $divisionId);
  }
}
