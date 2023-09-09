<?php

use Nsv\League\Core\LeagueAuthProvider;
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

final class LeagueAuthProviderTest extends TestCase 
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
  private Request $request;
  private LeagueAuthProvider $auth;

  public function setUp(): void {
    global $globals;
    $globals['masterpasswort'] = md5(self::MASTER_PASSWORD);

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
    $this->request = new Request();
    $this->request->setSession($this->session);
    $requestStack = new RequestStack();
    $requestStack->push($this->request);

    $this->auth = new LeagueAuthProvider($this->league, $requestStack);
  }

  public function testLegacyLogin_leagueManager_setsSesion() {
    $this->auth->legacyLogin($this->league, 't-xyz', self::LEAGUE_MANAGER_PW);

    $this->assertEquals(self::LEAGUE_ID, $this->session->get(LeagueAuthProvider::SESSION_KEY_LEAGUE));
    $this->assertEquals(0, $this->session->get(LeagueAuthProvider::SESSION_KEY_DIVISION));
    $this->assertEquals(time(), $this->session->get(LeagueAuthProvider::SESSION_KEY_LOGIN));  // TODO: I like to live dangerously :)
  }

  public function testLegacyLogin_divisionManager_setsSesion() {
    $this->auth->legacyLogin($this->league, 's-' . self::DIVISION_ID, self::DIVISION_MANAGER_PW);

    $this->assertEquals(self::LEAGUE_ID, $this->session->get(LeagueAuthProvider::SESSION_KEY_LEAGUE));
    $this->assertEquals(self::DIVISION_ID, $this->session->get(LeagueAuthProvider::SESSION_KEY_DIVISION));
    $this->assertEquals(time(), $this->session->get(LeagueAuthProvider::SESSION_KEY_LOGIN));  // TODO: I like to live dangerously :)
  }

  public function testLegacyLogin_masterPassword_setsSesion() {
    $this->auth->legacyLogin($this->league, 's-' . self::DIVISION_ID, self::MASTER_PASSWORD);

    $this->assertEquals(self::LEAGUE_ID, $this->session->get(LeagueAuthProvider::SESSION_KEY_LEAGUE));
    $this->assertEquals(self::DIVISION_ID, $this->session->get(LeagueAuthProvider::SESSION_KEY_DIVISION));
    $this->assertEquals(time(), $this->session->get(LeagueAuthProvider::SESSION_KEY_LOGIN));  // TODO: I like to live dangerously :)
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

  // TODO: test logout

  public function testAuthState_noSession_errorState() {
    $this->expectException(AccessDeniedHttpException::class);
    $this->expectExceptionMessage('Kein session cookie vorhanden');
    $this->auth->authState()->requireDivisionManager();
  }

  public function testAuthState_invalidSession_errorState() {
    $this->request->cookies->set($this->session->getName(), 'FakeSessionId');
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LOGIN, 'kein-int');

    $this->expectException(AccessDeniedHttpException::class);
    $this->expectExceptionMessage('Fehlerhafter session cookie');
    $this->auth->authState()->requireDivisionManager();
  }

  public function testAuthState_expiredSession_errorState() {
    $this->request->cookies->set($this->session->getName(), 'FakeSessionId');
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LOGIN, time() - 9 * 60 * 60);
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LEAGUE, self::LEAGUE_ID);
    $this->session->set(LeagueAuthProvider::SESSION_KEY_DIVISION, 0);

    $this->expectException(AccessDeniedHttpException::class);
    $this->expectExceptionMessage('Sitzung abgelaufen');
    $this->auth->authState()->requireDivisionManager();
  }

  public function testAuthState_wrongLeague_errorState() {
    $this->request->cookies->set($this->session->getName(), 'FakeSessionId');
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LOGIN, time());
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LEAGUE, self::LEAGUE_ID + 1);
    $this->session->set(LeagueAuthProvider::SESSION_KEY_DIVISION, 0);

    $this->expectException(AccessDeniedHttpException::class);
    $this->expectExceptionMessage('Kein Zugriff auf diese Liga');
    $this->auth->authState()->requireDivisionManager();
  }

  public function testAuthState_leagueManager_leagueManagerState() {
    $this->request->cookies->set($this->session->getName(), 'FakeSessionId');
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LOGIN, time());
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LEAGUE, self::LEAGUE_ID);
    $this->session->set(LeagueAuthProvider::SESSION_KEY_DIVISION, 0);

    $this->assertTrue($this->auth->authState()->isLeagueManager());
  }

  public function testAuthState_divisionManager_divisionManagerState() {
    $this->request->cookies->set($this->session->getName(), 'FakeSessionId');
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LOGIN, time());
    $this->session->set(LeagueAuthProvider::SESSION_KEY_LEAGUE, self::LEAGUE_ID);
    $this->session->set(LeagueAuthProvider::SESSION_KEY_DIVISION, self::DIVISION_ID);

    $division = $this->league->divisionById(self::DIVISION_ID);
    $this->assertTrue($this->auth->authState()->isDivisionManager($division));
  }
}
