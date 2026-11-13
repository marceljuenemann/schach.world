<?php

namespace Tests\Util;

use Nsv\Util\SchachInClient;
use Nsv\Util\SchachInEntity;
use PHPUnit\Framework\TestCase;

class SchachInEntityTest extends TestCase
{
  public static function setUpBeforeClass(): void {
    // SchachInEntity is declared in the same file as SchachInClient.
    class_exists(SchachInClient::class);
  }

  private function clubData(array $overrides = []): array {
    return array_merge([
      'alive' => true,
      'scope' => 'verein',
      'contact' => 'Testverein Hannover',
      'identifier' => 'testverein-hannover',
      'identifiers' => [
        ['category' => 'Sonstiges', 'identifier' => 'xyz'],
        ['category' => 'ZPS-Code', 'identifier' => '70101'],
      ],
      'url' => [['category' => 'Website', 'identification' => 'https://testverein.example.com']],
      'username' => [['category' => 'Instagram', 'username_url' => 'https://instagram.com/testverein']],
      'children' => [
        ['contacts' => [
          ['category' => 'Ansprechpartner', 'alive' => true, 'name' => 'Vorstand'],
          ['category' => 'Veranstaltungsort', 'alive' => false, 'name' => 'Altes Spiellokal'],
        ]],
        ['contacts' => [
          ['category' => 'Veranstaltungsort', 'alive' => true, 'name' => 'Spiellokal'],
        ]],
      ],
      'members' => 42,
      'members_u25' => 10,
      'members_female' => 5,
      'avg_byear' => 1980,
      'avg_rating' => 1650,
    ], $overrides);
  }

  public function testIsClub() {
    $this->assertTrue((new SchachInEntity($this->clubData()))->isClub());
    $this->assertTrue((new SchachInEntity($this->clubData(['scope' => 'verein-schachabteilung'])))->isClub());
    $this->assertFalse((new SchachInEntity($this->clubData(['scope' => 'bezirk'])))->isClub());
    $this->assertFalse((new SchachInEntity($this->clubData(['alive' => false])))->isClub());
  }

  public function testClubData() {
    $club = (new SchachInEntity($this->clubData()))->clubData();

    $this->assertSame('70101', $club->zps);
    $this->assertSame('701', $club->districtZps);
    $this->assertSame('Testverein Hannover', $club->name);
    $this->assertSame('https://schach.in/testverein-hannover', $club->detailsUri);
    $this->assertSame('https://www.schachbund.de/verein/70101.html', $club->dwzUri);
    $this->assertSame('https://testverein.example.com', $club->website);
    $this->assertSame('https://instagram.com/testverein', $club->instagramUri);
    $this->assertSame('Spiellokal', $club->venue['name']);
    $this->assertSame(42, $club->stats['members']);
    $this->assertSame(1650, $club->stats['avg_rating']);
    $this->assertSame((int) date('Y') - 1980, $club->stats['avg_age']);
  }

  public function testClubData_withoutOptionalData() {
    $data = $this->clubData(['children' => []]);
    unset($data['url'], $data['username']);
    $club = (new SchachInEntity($data))->clubData();

    $this->assertNull($club->website);
    $this->assertNull($club->instagramUri);
    $this->assertNull($club->venue);
  }

  public function testClubData_requiresZps() {
    $this->expectExceptionMessage('No ZPS found');
    (new SchachInEntity($this->clubData(['identifiers' => []])))->clubData();
  }
}
