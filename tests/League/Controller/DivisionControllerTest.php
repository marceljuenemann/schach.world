<?php

namespace Nsv\League\Application;

use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Snapshots\MatchesSnapshots;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DivisionControllerTest extends WebTestCase
{
  use MatchesSnapshots;

  #[DataProvider('statisticsTestCases')]
  public function testStatistics(string $league, string $division): void {
    $client = static::createClient();
    $client->request('GET', "/ligen/$league/$division/statistik");

    $this->assertResponseIsSuccessful();
    $this->assertMatchesSnapshot($client->getResponse()->getContent());
  }

  public static function statisticsTestCases(): \Generator {
    yield 'Standard league' => ['bezirk1-1718', 'kreisliga-ost'];
    yield 'Mutliple games per round' => ['sjbh-2324', 'bmm-u12'];
    yield 'No games' => ['sjbh-2021', 'bmm-u12'];
    // TODO: Currently, games won by forfeit are incorrectly counted
    // towards the game count in the top scorer table, see issue #63.
    yield 'Bug #63' => ['nsv-2425', 'verbandsliga-ost'];
  }
}
