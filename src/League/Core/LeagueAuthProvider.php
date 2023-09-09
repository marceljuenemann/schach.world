<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\League;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Manages user authorization and authentication for a specific league.
 */
class LeagueAuthProvider
{
  // Session keys for legacy user system.
  const SESSION_KEY_LOGIN = 'league_login_time';
  const SESSION_KEY_LEAGUE = 'league_login_league_id';
  const SESSION_KEY_DIVISION = 'league_login_division_id';
  const SESSION_DURATION = 8 * 60 * 60;  // 8 hours

  private Request $request;
  private Session $session;

  function __construct(private League $league, RequestStack $requestStack) {
    $this->request = $requestStack->getCurrentRequest();
    $this->session = $this->request->getSession();
  }

  /**
   * Provides the LeagueAuthState from the current request and session.
   */
  public function authState(): LeagueAuthState {
    if (!$this->request->hasPreviousSession()) {
      return LeagueAuthState::unauthorized('Kein session cookie vorhanden');
    }
    $loginTime = $this->session->get(self::SESSION_KEY_LOGIN);
    if (!is_numeric($loginTime)) {
      return LeagueAuthState::unauthorized('Fehlerhafter session cookie');
    }
    if ($loginTime < time() - self::SESSION_DURATION) {
      return LeagueAuthState::unauthorized('Sitzung abgelaufen');
    }
    if ($this->session->get(self::SESSION_KEY_LEAGUE) != $this->league->id) {
      return LeagueAuthState::unauthorized('Kein Zugriff auf diese Liga');
    }

    $divisionId = $this->session->get(self::SESSION_KEY_DIVISION);
    if ($divisionId) {
      return LeagueAuthState::divisionManager($this->league->divisionById($divisionId));
    } else {
      return LeagueAuthState::leagueManager();
    }
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
  function legacyLogin(string $user, string $password) {
    if ($user[0] == 's') {
      $divisionId = (int) substr($user, 2);
      $user = $this->league->divisionById($divisionId)->manager;
    } else {
      $divisionId = 0;
      $user = $this->league->manager;
    }

    global $globals;  // TODO: move master password somewhere else
    $pw = md5($password);
    if (!$user || ($pw !== $user->password && $pw !== $globals['masterpasswort'])) {
      throw new AccessDeniedHttpException("Falsches Passwort");
    }

    $this->session->set(self::SESSION_KEY_LOGIN, time());
    $this->session->set(self::SESSION_KEY_LEAGUE, $this->league->id);
    $this->session->set(self::SESSION_KEY_DIVISION, $divisionId);
  }

  function legacyLogout() {
    $this->session->clear();
  }
}
