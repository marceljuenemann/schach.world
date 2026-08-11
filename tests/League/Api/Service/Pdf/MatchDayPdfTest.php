<?php

namespace Nsv\League\Api\Service\Pdf;

use Nsv\League\Api\Service\MatchDayService;
use Nsv\League\Entity\Division;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Snapshots\MatchesSnapshots;
use Tests\League\LeagueTestCase;

class MatchDayPdfTest extends LeagueTestCase {
  use MatchesSnapshots;

  private MatchDayService $matchDayService;

  protected function setUp(): void {
    parent::setUp();
    $this->matchDayService = $this->container->get(MatchDayService::class);
  }

  public static function dataProvider(): \Generator {

    yield 'Common Case: 10 teams with 8 players' => ['nsv-2425', 'landesliga-sued', 5];
    yield 'Ranking in sidebar' => ['sjbh-1718', 'bmm-u12', 5];
    yield 'Multiple Pages' => ['sjbh-2425', 'bmm-u12', 5];
    yield 'Many comments' => ['pokal-1516', 'pokal-mm', 1];
    yield 'Long comment' => ['sjbh-2526', 'bmm-u12', 6, true];  // TODO: Fix
    yield 'Long player name' => ['fbl-1314', '2-frauen-bl-sued', 6];
    // TODO: Ausrichter
    // TODO: Verlegt
    // TODO: Long team name
  }

  /**
   * Snapshot-Test for generated PDF.
   */
  #[DataProvider('dataProvider')]
  public function testPdfGeneration($leaguePath, $divisionPath, $round, $longComment = false): void {
    $league = $this->leagueRepository->findByPathOrPrefix($leaguePath);
    $division = $league->divisionByPath($divisionPath);
    $matchDay = $this->matchDayService->matchDay($division, $round);

    if ($longComment) {
      $matchDay->comment = str_repeat("Lorem ipsum dolor sit amet, consectetur adipiscing elit.\n\n", 7);
    }

    $pdf = new MatchDayPdf($division, $matchDay, 'https://localhost:6464');
    $pdf->render();
    $response = $pdf->getResponse();

    // FPDF embeds the current time as /CreationDate; normalize it to a fixed
    // value so the snapshot doesn't change on every run.
    $content = preg_replace(
      "/\/CreationDate \(D:\d{14}[+-]\d{2}'\d{2}'\)/",
      "/CreationDate (D:20260101000000+00'00')",
      $response->getContent(),
    );

    $tmpFile = tempnam(sys_get_temp_dir(), 'pdf') . '.pdf';
    file_put_contents($tmpFile, $content);
    $this->assertMatchesFileSnapshot($tmpFile);
  }
}