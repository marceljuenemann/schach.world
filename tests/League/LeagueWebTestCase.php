<?php

namespace Tests\League;

use Nsv\League\Core\LeagueAuthProvider;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class for functional tests that send HTTP requests to the league controllers.
 *
 * The legacy system in public/ligen/ reads superglobals ($_GET, $_POST, $_SERVER) directly,
 * which the BrowserKit client doesn't populate. request() takes care of that.
 */
abstract class LeagueWebTestCase extends WebTestCase
{
  protected const LEAGUE = 'nsv-2526';
  protected const DIVISION_ID = 1858;
  protected const TEAM_ID = 8324;
  protected const PLAYER_ID = 538122;

  protected KernelBrowser $client;
  private array $originalServer;

  protected function setUp(): void {
    $this->client = static::createClient();
    $this->originalServer = $_SERVER;
  }

  protected function tearDown(): void {
    // The legacy system keeps login state in a global variable.
    unset($GLOBALS['admin']);
    $_GET = [];
    $_POST = [];
    $_SERVER = $this->originalServer;
    parent::tearDown();
  }

  /**
   * Sends a request and returns the response. Pass $json to send a JSON request body.
   */
  protected function request(string $method, string $uri, array $parameters = [], mixed $json = null, array $headers = []): Response {
    parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $query);
    $_GET = $method === 'GET' ? array_merge($query, $parameters) : $query;
    $_POST = $method === 'POST' ? $parameters : [];
    $_SERVER['REQUEST_URI'] = $uri;
    $_SERVER['PHP_SELF'] = parse_url($uri, PHP_URL_PATH);
    $_SERVER['REQUEST_SCHEME'] = 'http';
    $_SERVER['HTTP_HOST'] = 'localhost';

    $server = $headers;
    $content = null;
    if ($json !== null) {
      $server['CONTENT_TYPE'] = 'application/json';
      $content = json_encode($json);
    }
    $this->client->request($method, $uri, $parameters, [], $server, $content);
    return $this->client->getResponse();
  }

  protected function league(string $path = self::LEAGUE): League {
    return static::getContainer()->get(LeagueRepository::class)->findByPathOrPrefix($path);
  }

  /**
   * Logs in as league manager, or as division manager if a division is passed.
   */
  protected function login(?Division $division = null, ?League $league = null, ?int $loginTime = null): void {
    $league ??= $division?->league ?? $this->league();
    $session = static::getContainer()->get('session.factory')->createSession();
    $session->set(LeagueAuthProvider::SESSION_KEY_LOGIN, $loginTime ?? time());
    $session->set(LeagueAuthProvider::SESSION_KEY_LEAGUE, $league->id);
    $session->set(LeagueAuthProvider::SESSION_KEY_DIVISION, $division?->id ?? 0);
    $session->save();
    $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
  }

  protected function assertHtmlContains(string $needle, Response $response): void {
    $this->assertStringContainsString($needle, $response->getContent());
  }
}
