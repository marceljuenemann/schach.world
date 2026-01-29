<?php

namespace League\Api\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Snapshots\MatchesSnapshots;

class StatisticsServiceApplicationTest extends WebTestCase {

  use MatchesSnapshots;

  protected function setUp(): void {
    $this->client = static::createClient();
    $this->baseUrl = 'https://nsv-online.local/ligen/';
  }

  public static function divisionDataProvider(): \Generator {
    yield 'Bezirk Hannover Kreisliga Ost 17/18' => ['bezirk1-1718', 'kreisliga-ost'];
    yield 'Bezirk Hannover Bezirksliga 18/19' => ['bezirk1-1819', 'bezirksliga'];
    yield 'Bezirk 3 Bezirksklasse 21/22' => ['bezirk3-2122', 'bezirksklasse'];
    yield 'Landesliga Süd 21/22' => ['nsv-2122', 'landesliga-sued'];
    yield 'Verbandsliga Nord 22/23' => ['nsv-2223', 'verbandsliga-nord'];
  }

  #[DataProvider('divisionDataProvider')]
  public function testFullStatisticsHtml($league, $division): void
  {
    $uri = '/ligen/' . $league . '/' . $division . '/statistik';
    $crawler = $this->client->request('GET', $uri);
    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h1', 'Symfony Test');
    $statisticsContent = $crawler->filter('#nsv-main .nsv-card:not(.nsv-sidebar-card) .card-body');
    foreach ($statisticsContent as $domElement) {
      foreach($domElement->childNodes as $node) {
        $html .= $domElement->ownerDocument->saveHTML($node);
      }
    }
    $this->assertMatchesSnapshot($html);
  }
}