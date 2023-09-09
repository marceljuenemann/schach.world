<?php

namespace Nsv\League\Core;

use Exception;
use Nsv\League\Entity\Division;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Authentication and authorization state for the current league.
 * Should be the main way to check for permissions.
 */
class LeagueAuthState
{
  function __construct(
    private bool $isManager,
    private Division|null $_managedDivision,
    private string|null $errorMessage
  ) {}

  /**
   * Whether the current user is a league manager with access to all
   * settings for the league.
   */
  public function isLeagueManager(): bool {
    return $this->isManager && $this->_managedDivision === null;
  }

  /**
   * Whether the current user is a division or league manager for this
   * league. If a Division entity is passed, this method checks whether
   * the user may manage that specific division.
   */
  public function isDivisionManager(Division|null $division = null): bool {
    return $this->isManager && ($division === null || $this->_managedDivision === null || $this->_managedDivision === $division);
  }

  /**
   * Verifies that the current user is a division or league manager for the
   * current league. If a division is passed, they need to have permission
   * for managing the specified division.
   */
  public function requireDivisionManager(Division|null $division = null): void {
    if (!$this->isManager) {
      throw new AccessDeniedHttpException($this->errorMessage);
    }
    if (!$this->isDivisionManager($division)) {
      throw new AccessDeniedHttpException('Kein Zugriff auf diese Staffel');
    }
  }

  /**
   * Verifies that the current user is a league manager for the
   * current league.
   */
  public function requireLeagueManager(): void {
    if (!$this->isManager) {
      throw new AccessDeniedHttpException($this->errorMessage);
    }
    if (!$this->isLeagueManager()) {
      throw new AccessDeniedHttpException('Turnierleiter:innen Zugriff erforderlich.');
    }
  }

  /**
   * Returns the Division that this user manages.
   */
  public function managedDivision(): Division {
    if (!$this->_managedDivision instanceof Division) {
      throw new Exception('Must manage a Division');
    }
    return $this->_managedDivision;
  }

  public static function unauthorized(string $errorMessage): LeagueAuthState {
    return new LeagueAuthState(false, null, $errorMessage);
  }

  public static function leagueManager(): LeagueAuthState {
    return new LeagueAuthState(true, null, null);
  }

  public static function divisionManager(Division $division): LeagueAuthState {
    return new LeagueAuthState(true, $division, null);
  }
}
