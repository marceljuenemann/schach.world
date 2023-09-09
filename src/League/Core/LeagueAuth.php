<?php

namespace Nsv\League\Core;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Team;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * User authorization and authentication for a specific league.
 */
class LeagueAuth
{
  // Session keys for legacy user system.
  const SESSION_KEY_LOGIN = 'league_login_time';
  const SESSION_KEY_LEAGUE = 'league_login_league_id';
  const SESSION_KEY_DIVISION = 'league_login_division_id';
  const SESSION_DURATION = 8 * 60 * 60;  // 8 hours

  private Session $session;
  private bool $isManager = false;
  private Division|null $managedDivision;
  private string $errorMessage;

  function __construct(private League $league, private RequestStack $requestStack) {
    $request = $requestStack->getCurrentRequest();
    $this->session = $request->getSession();

    if (!$request->hasPreviousSession()) {
      $this->errorMessage = 'Session Cookie fehlt';
      return;
    }
    $loginTime = $this->session->get(self::SESSION_KEY_LOGIN);
    if (!is_numeric($loginTime)) {
      $this->errorMessage = 'Fehlerhafter Session Cookie';
      return;
    }
    if ($loginTime < time() - self::SESSION_DURATION) {
      $this->errorMessage = 'Session abgelaufen';
      return;
    }
    if ($this->session->get(self::SESSION_KEY_LEAGUE) != $league->id) {
      $this->errorMessage = 'Kein Zugriff auf diese Liga';
      return;
    }

    $divisionId = $this->session->get(self::SESSION_KEY_DIVISION);
    $this->managedDivision = $divisionId ? $league->divisionById($divisionId) : null;
    $this->isManager = true;
  }

  /**
   * Whether the current user is a league manager with access to all
   * settings for the league.
   */
  public function isLeagueManager(): bool {
    return $this->isManager && $this->managedDivision === null;
  }

  /**
   * Whether the current user is a division manager for this league. If a 
   * Division entity is passed, this method checks whether the user
   * may manage that specific division.
   */
  public function isDivisionManager(Division|null $division = null): bool {
    return $this->isManager && ($division === null || $this->managedDivision === null || $this->managedDivision === $division);
  }

  /**
   * Whether the current user may manage the given team.
   */
  public function isTeamManager(Team $team): bool {
    // TODO: check for team auth code.
    // Note: If team's division is null, all division managers should be allowed.
    return $this->isDivisionManager($team->division);
  }

  /**
   * TODO replace checkManagerAccess with:
   * - requireDivisionManager(Division|null)
   * - requireLeagueManager
   * - requireTeamManager
   */

  /**
   * Verifies that the current user is a manager for the given league.
   * 
   * @return Division|null the division for which the user is authorized, or null
   *    if the user may manage all divisions.
   * @throws AccessDeniedHttpException if verification failed.
   */
  // TODO: Replace method.
  public function checkManagerAccess(): Division|null {
    if ($this->isManager) {
      return $this->managedDivision;      
    } else {
      throw new AccessDeniedHttpException($this->errorMessage);
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
  function legacyLogin(League $league, string $user, string $password) {
    if ($user[0] == 's') {
      $divisionId = (int) substr($user, 2);
      $user = $league->divisionById($divisionId)->manager;
    } else {
      $divisionId = 0;
      $user = $league->manager;
    }

    global $globals;  // TODO: move master password somewhere else
    $pw = md5($password);
    if (!$user || ($pw !== $user->password && $pw !== $globals['masterpasswort'])) {
      throw new AccessDeniedHttpException("Falsches Passwort");
    }

    $this->session->set(self::SESSION_KEY_LOGIN, time());
    $this->session->set(self::SESSION_KEY_LEAGUE, $league->id);
    $this->session->set(self::SESSION_KEY_DIVISION, $divisionId);
  }

  function legacyLogout() {
    $this->session->clear();
  }
}
