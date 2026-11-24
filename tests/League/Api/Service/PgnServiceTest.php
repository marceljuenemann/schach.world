<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Core\Result;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Pairing;
use Nsv\League\Repository\PairingRepository;
use Tests\League\LeagueTestCase;

class PgnServiceTest extends LeagueTestCase
{
  private const BOM = "\xEF\xBB\xBF";

  private PgnService $service;
  private League $league;
  private Division $division;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(PgnService::class);
    $this->league = $this->leagueRepository->findByPathOrPrefix('nsv-2526');
    $this->division = $this->league->divisionByPath('landesliga-nord');
  }

  protected function tearDown(): void {
    // Some tests modify entities in memory. Make sure these changes are never flushed.
    $this->em->clear();
    $this->container->get('doctrine')->getManagerForClass(Pairing::class)->clear();
    parent::tearDown();
  }

  public function testRenderPgn_round1() {
    $pgn = $this->service->renderPgn($this->division, $this->division->round(1));
    $this->assertStringStartsWith(self::BOM, $pgn);
    $this->assertMatchesTextSnapshot($pgn);
  }

  public function testRenderPgn_roundWithoutGames() {
    $this->assertSame(self::BOM, $this->service->renderPgn($this->division, $this->division->round(9)));
  }

  public function testRenderPgn_incompleteGames() {
    $round = $this->division->round(1);
    $gameCount = substr_count($this->service->renderPgn($this->division, $round), '[Event ');

    // Re-querying would reload the players from the database, so render the modified pairings
    // through a service whose repository returns them as they are.
    $pairings = $this->container->get(PairingRepository::class)->findByRound($this->division, 1);
    $games = array_values($pairings[0]->games->toArray());
    $games[0]->result1 = Result::UNKNOWN;
    $games[0]->result2 = Result::UNKNOWN;
    $games[1]->player2 = null;
    $games[2]->player1 = null;
    $repository = $this->createStub(PairingRepository::class);
    $repository->method('findByRound')->willReturn($pairings);

    $pgn = (new PgnService($repository))->renderPgn($this->division, $round);

    // Games with a missing player are skipped, without affecting the board numbers of others.
    $this->assertSame($gameCount - 2, substr_count($pgn, '[Event '));
    $this->assertStringContainsString('[Round "1.1.' . $games[0]->board . '"]', $pgn);
    $this->assertStringNotContainsString('[Round "1.1.' . $games[1]->board . '"]', $pgn);
    $this->assertStringNotContainsString('[Round "1.1.' . $games[2]->board . '"]', $pgn);
    $this->assertStringContainsString('[Round "1.1.' . $games[3]->board . '"]', $pgn);
    // Games without a result are marked as ongoing.
    $this->assertSame(1, substr_count($pgn, '[Result "*"]'));
    $this->assertStringContainsString("[Result \"*\"]\n\n*\n\n", $pgn);
  }
}
