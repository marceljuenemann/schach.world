<?php

use Nsv\League\Core\Auth;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\LegacyUser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AuthTest extends TestCase 
{
  const LEAGUE_ID = 23;
  const LEAGUE_MANAGER_ID = 42;
  const LEAGUE_MANAGER_PW ='123456';
  const DIVISION_ID = 1;
  const DIVISION_MANAGER_ID = 2;
  const DIVISION_MANAGER_PW ='123456b';
  const MASTER_PASSWORD = 'abcdef';
  const WRONG_PASSWORD = '654321';

  private League $league;
  private Session $session;
  private Auth $auth;

  public function setUp(): void {
    $division = new Division();
    $division->id = self::DIVISION_ID;
    $division->manager = new LegacyUser();
    $division->manager->id = self::DIVISION_MANAGER_ID;
    $division->manager->password = md5(self::DIVISION_MANAGER_PW);

    $this->league = new League();
    $this->league->id = self::LEAGUE_ID;
    $this->league->divisions = [$division];
    $this->league->manager = new LegacyUser();
    $this->league->manager->id = self::LEAGUE_MANAGER_ID;
    $this->league->manager->password = md5(self::LEAGUE_MANAGER_PW);

    $this->session = new Session(new MockArraySessionStorage());
    $request = new Request();
    $request->setSession($this->session);
    $requestStack = new RequestStack();
    $requestStack->push($request);
    $this->auth = new Auth($requestStack);

    global $globals;
    $globals['masterpasswort'] = md5(self::MASTER_PASSWORD);
  }

  public function testLegacyLogin_leagueManager_setsSesion() {
    $this->auth->legacyLogin($this->league, 't-xyz', self::LEAGUE_MANAGER_PW);

    $this->assertEquals(self::LEAGUE_ID, $this->session->get(Auth::SESSION_KEY_LEAGUE));
    $this->assertEquals(0, $this->session->get(Auth::SESSION_KEY_DIVISION));
    $this->assertEquals(time(), $this->session->get(Auth::SESSION_KEY_LOGIN));  // TODO: I like to live dangerously :)
  }

  public function testLegacyLogin_divisionManager_setsSesion() {
    $this->auth->legacyLogin($this->league, 's-' . self::DIVISION_ID, self::DIVISION_MANAGER_PW);

    $this->assertEquals(self::LEAGUE_ID, $this->session->get(Auth::SESSION_KEY_LEAGUE));
    $this->assertEquals(self::DIVISION_ID, $this->session->get(Auth::SESSION_KEY_DIVISION));
    $this->assertEquals(time(), $this->session->get(Auth::SESSION_KEY_LOGIN));  // TODO: I like to live dangerously :)
  }

  public function testLegacyLogin_masterPassword_setsSesion() {
    $this->auth->legacyLogin($this->league, 's-' . self::DIVISION_ID, self::MASTER_PASSWORD);

    $this->assertEquals(self::LEAGUE_ID, $this->session->get(Auth::SESSION_KEY_LEAGUE));
    $this->assertEquals(self::DIVISION_ID, $this->session->get(Auth::SESSION_KEY_DIVISION));
    $this->assertEquals(time(), $this->session->get(Auth::SESSION_KEY_LOGIN));  // TODO: I like to live dangerously :)
  }

  public function testLegacyLogin_wrongPassword_throwsException() {
    $this->expectException(AccessDeniedHttpException::class);
    $this->auth->legacyLogin($this->league, 's-' . self::DIVISION_ID, self::WRONG_PASSWORD);
  }

  public function testLegacyLogin_wrongPasswordForLeagueManager_throwsException() {
    $this->expectException(AccessDeniedHttpException::class);
    $this->auth->legacyLogin($this->league, 't-xyz', self::WRONG_PASSWORD);
  }

  public function testLegacyLogin_unkownDivision_throwsException() {
    $this->expectException(NotFoundHttpException::class);
    $this->auth->legacyLogin($this->league, 's-' . (self::DIVISION_ID + 1), self::DIVISION_MANAGER_PW);
  }

  public function testCheckManagerAccess_leagueManagerLoggedIn_returnsNull() {
    $this->auth->legacyLogin($this->league, 't-xyz', self::LEAGUE_MANAGER_PW);

    $this->assertNull($this->auth->checkManagerAccess($this->league));
  }

  public function testCheckManagerAccess_divisionManagerLoggedIn_returnsDivision() {
    $this->auth->legacyLogin($this->league, 's-' . self::DIVISION_ID, self::DIVISION_MANAGER_PW);

    $this->assertSame($this->league->divisionById(self::DIVISION_ID), $this->auth->checkManagerAccess($this->league));
  }

  public function testCheckManagerAccess_notLoggedIn_throwsException() {
    $this->expectException(AccessDeniedHttpException::class);
    $this->auth->checkManagerAccess($this->league);
  }

  public function testCheckManagerAccess_sessionExpired_throwsException() {
    $this->session->set(Auth::SESSION_KEY_LOGIN, time() - 9 * 60 * 60);
    $this->session->set(Auth::SESSION_KEY_LEAGUE, self::LEAGUE_ID);
    $this->session->set(Auth::SESSION_KEY_DIVISION, 0);

    $this->expectException(AccessDeniedHttpException::class);
    $this->auth->checkManagerAccess($this->league);
  }

  public function testCheckManagerAccess_wrongLeague_throwsException() {
    $this->session->set(Auth::SESSION_KEY_LOGIN, time());
    $this->session->set(Auth::SESSION_KEY_LEAGUE, self::LEAGUE_ID + 1);
    $this->session->set(Auth::SESSION_KEY_DIVISION, 0);

    $this->expectException(AccessDeniedHttpException::class);
    $this->auth->checkManagerAccess($this->league);
  }
}
