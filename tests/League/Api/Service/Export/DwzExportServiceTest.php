<?php

namespace Tests\League\Api\Service\Export;

use DOMDocument;
use Nsv\League\Api\Service\Export\DwzExportService;
use Nsv\League\Core\Result;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Game;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Pairing;
use SimpleXMLElement;
use Tests\League\LeagueTestCase;

/**
 * Some tests modify entities in memory to cover edge cases. These changes are never flushed.
 */
class DwzExportServiceTest extends LeagueTestCase
{
  private const XSD = __DIR__ . '/../../../../../src/League/Api/Service/Export/DSB_DWZ_Tounament_2_5_1.xsd';

  private DwzExportService $service;
  private League $league;
  private Division $division;
  private ?string $originalHost;

  protected function setUp(): void {
    parent::setUp();
    $this->service = new DwzExportService();
    $this->league = $this->leagueRepository->findByPathOrPrefix('nsv-2526');
    $this->division = $this->league->divisionByPath('landesliga-nord');
    $this->originalHost = $_SERVER['HTTP_HOST'] ?? null;
    $_SERVER['HTTP_HOST'] = 'nsv-online.de';
  }

  protected function tearDown(): void {
    $this->em->clear();
    $_SERVER['HTTP_HOST'] = $this->originalHost;
    parent::tearDown();
  }

  private function export(array $divisions): SimpleXMLElement {
    $xml = $this->service->generateXml($this->league, $divisions);
    $this->assertValidXml($xml);
    $element = simplexml_load_string($xml);
    $element->registerXPathNamespace('d', 'http://www.schachbund.de/XMLSchema');
    return $element;
  }

  private function assertValidXml(string $xml): void {
    $dom = new DOMDocument();
    $dom->loadXML($xml);
    $previous = libxml_use_internal_errors(true);
    $valid = $dom->schemaValidate(self::XSD);
    $errors = array_map(fn($e) => trim($e->message) . " (line {$e->line})", libxml_get_errors());
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $this->assertTrue($valid, "XSD validation failed:\n" . implode("\n", $errors));
  }

  private function playerBySurname(SimpleXMLElement $xml, string $surname): SimpleXMLElement {
    return $xml->xpath("//d:player[d:surname='$surname']")[0];
  }

  /** Returns the first pairing of round 1 with all results entered. */
  private function playedPairing(): Pairing {
    foreach ($this->division->pairings as $pairing) {
      if ($pairing->round === 1 && count($pairing->games) >= 4) {
        return $pairing;
      }
    }
    $this->fail('No played pairing found');
  }

  public function testGenerateXml() {
    $xml = $this->export([$this->division]);

    $this->assertSame('2.5', (string) $xml['version']);
    $this->assertSame('Norddeutscher Schachverband', (string) $xml->header->sender);
    $this->assertSame('schach.world', (string) $xml->header->system);
    $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', (string) $xml->header->fileUUID);

    $tournament = $xml->tournament;
    $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-5[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', (string) $tournament->tournamentUUID);
    $this->assertSame('Landes- und Verbandsligen 2025/26', (string) $tournament->label);
    $this->assertSame('TR', (string) $tournament->tournamentType);
    $this->assertSame('2025-09-28', (string) $tournament->startDate);
    $this->assertSame('https://nsv-online.de/ligen/nsv-2526/', (string) $tournament->url);

    // All games reference exported players.
    $playerNumbers = array_map('strval', $xml->xpath('//d:player/d:tournamentPlayerNumber'));
    $this->assertSame(range(1, count($playerNumbers)), array_map('intval', $playerNumbers));
    $this->assertNotEmpty($xml->games->game);
    foreach ($xml->games->game as $game) {
      $this->assertContains((string) $game->tournamentPlayerNumberWhite, $playerNumbers);
      $this->assertContains((string) $game->tournamentPlayerNumberBlack, $playerNumbers);
      $this->assertContains((string) $game->pointWhite, ['1', '0.5', '0', '+', '-']);
    }
    $this->assertSame((int) $tournament->rounds, max(array_map('intval', $xml->xpath('//d:game/d:round'))));
  }

  public function testGenerateXml_snapshot() {
    $xml = $this->service->generateXml($this->league, [$this->division]);

    // Replace the values that change on every export.
    $xml = preg_replace('#<creationDate>[^<]+</creationDate>#', '<creationDate>CREATION_DATE</creationDate>', $xml, count: $dates);
    $xml = preg_replace('#<fileUUID>[^<]+</fileUUID>#', '<fileUUID>FILE_UUID</fileUUID>', $xml, count: $uuids);
    $this->assertSame([1, 1], [$dates, $uuids]);

    $this->assertMatchesXmlSnapshot($xml);
  }

  public function testGenerateXml_tournamentUuidIsStable() {
    $first = $this->export([$this->division]);
    $second = $this->export([$this->division]);
    $this->assertSame((string) $first->tournament->tournamentUUID, (string) $second->tournament->tournamentUUID);
    $this->assertNotSame((string) $first->header->fileUUID, (string) $second->header->fileUUID);
  }

  public function testGenerateXml_multipleDivisions() {
    $single = $this->export([$this->division]);
    $multiple = $this->export([$this->division, $this->league->divisionByPath('verbandsliga-nord')]);

    $this->assertGreaterThan(count($single->players->player), count($multiple->players->player));
    $this->assertGreaterThan(count($single->games->game), count($multiple->games->game));
  }

  public function testGenerateXml_divisionOfOtherLeague() {
    $other = $this->leagueRepository->findByPathOrPrefix('nsj-2526')->divisions[0];
    $this->expectException(\InvalidArgumentException::class);
    $this->service->generateXml($this->league, [$this->division, $other]);
  }

  public function testGenerateXml_byesAndIncompleteGames() {
    /** @var Game[] $games */
    $games = array_values($this->playedPairing()->games->toArray());
    $countBefore = count($this->export([$this->division])->games->game);

    // Bye with missing black player.
    $games[0]->result1 = Result::BYE_WIN;
    $games[0]->result2 = Result::BYE_LOSS;
    $games[0]->player2 = null;
    // Bye with missing white player.
    $games[1]->result1 = Result::BYE_LOSS;
    $games[1]->result2 = Result::BYE_WIN;
    $games[1]->player1 = null;
    // Not entered yet: skipped.
    $games[2]->result1 = Result::UNKNOWN;
    // No players at all: skipped.
    $games[3]->player1 = null;
    $games[3]->player2 = null;

    $xml = $this->export([$this->division]);

    $this->assertCount($countBefore - 2, $xml->games->game);
    $noBlack = $xml->xpath('//d:game[d:noneBlackPlayer]');
    $this->assertCount(1, $noBlack);
    $this->assertSame('true', (string) $noBlack[0]->noneBlackPlayer);
    $this->assertSame('+', (string) $noBlack[0]->pointWhite);
    $this->assertSame('-', (string) $noBlack[0]->pointBlack);
    $noWhite = $xml->xpath('//d:game[d:noneWhitePlayer]');
    $this->assertCount(1, $noWhite);
    $this->assertSame('+', (string) $noWhite[0]->pointBlack);
  }

  public function testGenerateXml_playerWithIncompleteData() {
    $player = array_values($this->playedPairing()->games->toArray())[0]->player1;
    $player->zps = 'unknown';
    $player->elo = 9999;
    $player->gender = 'd';
    $player->birth = '';

    $element = $this->playerBySurname($this->export([$this->division]), $player->lastName);

    $this->assertSame('1900', (string) $element->dobYear);
    $this->assertFalse(isset($element->vkz));
    $this->assertFalse(isset($element->numberClubMember));
    $this->assertFalse(isset($element->fideRating));
    $this->assertFalse(isset($element->sex));
  }

  public function testGenerateXml_memberNumberOutOfRange() {
    $player = array_values($this->playedPairing()->games->toArray())[0]->player1;
    $player->zps = '70101-0';
    $player->gender = 'w';

    $element = $this->playerBySurname($this->export([$this->division]), $player->lastName);

    $this->assertSame('70101', (string) $element->vkz);
    $this->assertFalse(isset($element->numberClubMember));
    $this->assertSame('w', (string) $element->sex);
  }

  public function testGenerateXml_unknownResult() {
    array_values($this->playedPairing()->games->toArray())[0]->result1 = 'x';
    $this->expectException(\UnexpectedValueException::class);
    $this->service->generateXml($this->league, [$this->division]);
  }
}
