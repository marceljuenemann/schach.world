<?php

namespace Tests\WebApp\Controller;

use Nsv\WebApp\Controller\ClubController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Tester\CommandTester;

require_once __DIR__ . '/../../Support/wordpress-stubs.php';

/**
 * The club list is fetched from schach.in and cached. To avoid network requests, this test
 * fills the cache with fake data, and restores the previous cache entry afterwards.
 */
class ClubControllerTest extends WebTestCase
{
  private KernelBrowser $client;
  private FilesystemAdapter $cache;
  private mixed $previousCacheValue = null;

  protected function setUp(): void {
    $this->client = static::createClient();
    $projectDir = static::getContainer()->getParameter('kernel.project_dir');
    $this->cache = new FilesystemAdapter(ClubController::CACHE_NAMESPACE, 0, $projectDir . ClubController::CACHE_DIR);

    $item = $this->cache->getItem(ClubController::CACHE_KEY);
    $this->previousCacheValue = $item->isHit() ? $item->get() : null;
    $this->cache->save($item->set($this->fakeDistricts()));
  }

  protected function tearDown(): void {
    if ($this->previousCacheValue !== null) {
      $item = $this->cache->getItem(ClubController::CACHE_KEY);
      $this->cache->save($item->set($this->previousCacheValue)->expiresAfter(ClubController::CACHE_EXPIRATION));
    } else {
      $this->cache->deleteItem(ClubController::CACHE_KEY);
    }
    parent::tearDown();
  }

  private function fakeDistricts(): array {
    $club = new \stdClass();
    $club->zps = '70101';
    $club->districtZps = '701';
    $club->name = 'Testverein Hannover';
    $club->detailsUri = 'https://schach.in/testverein';
    $club->dwzUri = 'https://www.schachbund.de/verein/70101.html';
    $club->website = 'https://testverein.example.com';
    return [
      '701' => [
        'name' => 'Bezirk Hannover',
        'website' => 'https://schachbezirk-hannover.de',
        'markerColor' => 'blue',
        'clubs' => ['70101' => $club],
      ],
      '702' => [
        'name' => 'Bezirk Braunschweig',
        'website' => 'https://schachbezirk-braunschweig.de',
        'markerColor' => 'green',
        'clubs' => [],
      ],
    ];
  }

  public function testClubs() {
    $crawler = $this->client->request('GET', '/vereine/');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h2', 'Vereine');
    $this->assertSelectorTextContains('h4', 'Bezirk Hannover');
    $this->assertSelectorTextContains('table', 'Testverein Hannover');
    $this->assertSelectorExists('a[href="https://testverein.example.com"]');

    $data = json_decode($crawler->filter('[data-nsv-component="ClubsMap"]')->attr('data-nsv-data'), true);
    $this->assertSame('Testverein Hannover', $data['districts']['701']['clubs']['70101']['name']);
    $this->assertSame((float) $_ENV['CLUBS_LAT'], (float) $data['lat']);
  }

  public function testClubsApi() {
    $this->client->request('GET', '/vereine/api/');

    $this->assertResponseIsSuccessful();
    $data = json_decode($this->client->getResponse()->getContent(), true);
    $this->assertSame(['701', '702'], array_map('strval', array_keys($data)));
    $this->assertSame('70101', $data['701']['clubs']['70101']['zps']);
  }

  public function testCacheClearCommand() {
    $application = new Application(static::$kernel);
    $tester = new CommandTester($application->find('nsv:cache:clear'));
    $tester->execute([]);

    $tester->assertCommandIsSuccessful();
    $this->assertStringContainsString('Clearing NSV cache...', $tester->getDisplay());
    $this->assertFalse($this->cache->getItem(ClubController::CACHE_KEY)->isHit());
  }
}
