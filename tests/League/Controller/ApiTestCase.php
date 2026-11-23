<?php

namespace Tests\League\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Core\LeagueAuthProvider;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Tests\League\CreatesPairing;

/**
 * Sends real requests to the league API, using a pairing and teams created for the test.
 */
abstract class ApiTestCase extends WebTestCase
{
  use CreatesPairing;

  protected KernelBrowser $client;
  protected EntityManagerInterface $em;
  protected League $league;
  protected League $otherLeague;
  protected Division $division;

  protected function setUp(): void {
    $this->client = static::createClient();
    $this->client->disableReboot();
    $repository = $this->client->getContainer()->get(LeagueRepository::class);
    $this->em = $repository->getEntityManager();
    $this->league = $repository->findByPathOrPrefix('bezirk1-2526');
    $this->otherLeague = $repository->findByPathOrPrefix('nsj-2526');
    foreach ($this->league->divisions as $division) {
      if ($division->boardCount() >= 2) $this->division = $division;
    }
    $this->createPairing($this->em, $this->league, $this->division);
  }

  protected function tearDown(): void {
    $this->deleteCreatedPairing($this->em);
    parent::tearDown();
  }

  protected function login(League $league, ?Division $division = null): void {
    $session = $this->client->getContainer()->get('session.factory')->createSession();
    $session->set(LeagueAuthProvider::SESSION_KEY_LOGIN, time());
    $session->set(LeagueAuthProvider::SESSION_KEY_LEAGUE, $league->id);
    $session->set(LeagueAuthProvider::SESSION_KEY_DIVISION, $division?->id ?? 0);
    $session->save();
    $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
  }

  protected function api(string $method, string $path, ?array $payload = null, ?League $league = null): array {
    $league ??= $this->league;
    $uri = "/ligen/{$league->path}/api/$path";
    $server = ['CONTENT_TYPE' => 'application/json', 'HTTPS' => 'on'];
    $this->client->request($method, $uri, server: $server, content: json_encode($payload));
    return json_decode($this->client->getResponse()->getContent(), true) ?? [];
  }
}
