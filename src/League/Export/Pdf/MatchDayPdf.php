<?php

namespace Nsv\League\Export\Pdf;

use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Text;

/**
 * TODO:
 * - Adjust font size for long names (pairings and ranking)
 * - Render a footer
 * - Test in production (pdf-ng)
 * - URIs
 * - Fine tune player number width
 * - Add tests
 *   - NSV (5 x 8)
 *   - U12 (6 x 2 x 4)
 *   - Two matches
 *   - Multi page
 *   - Edge cases (comments)
 *      - Lots of comments https://localhost:6464/ligen/pokal-1516/pokal-mm/1/
 *      - Not enough Y space for table https://localhost:6464/ligen/sjbh-1718/bmm-u12/5/pdf-ng/
 *      - Table not on page 2 https://localhost:6464/ligen/sjbh-2526/bmm-u12/3/pdf-ng/
 *      - Table too wide
 *      - Comments with new line
 *      - Comments with HTML (ignore)
 * - Move into Service directory
 * - Move Service directory out of Api/
 */
class MatchDayPdf {
  private const SIDEBAR_WIDTH = 60;
  private const SIDEBAR_PADDING = 3;

  private Pdf $pdf;
  private PairingList $pairingList;
  private Ranking | null $ranking = null;

  public function __construct(private Division $division, private MatchDay $matchDay) {
    $this->pdf = new Pdf();
    $this->pairingList = new PairingList($matchDay);
    if (!empty($matchDay->legacyRanking)) {
      $this->ranking = new Ranking($matchDay->legacyRanking);
    }
  }

  public function render() {
    // 1. Render header.
    // TODO: Footer
    $this->renderHeader();

    // 2. Render pairing list.
    $yPairingList = $this->pdf->GetY();
    $this->pdf->with(
      ['rMargin' => $this->pdf->GetRightMargin() + self::SIDEBAR_WIDTH + self::SIDEBAR_PADDING],
      fn() => $this->pdf->render($this->pairingList)
    );

    // 3. Layout the ranking table.
    $rankingInSidebar = false;
    if ($this->ranking) {
      $this->ranking->layout($this->pdf);
      $rankingInSidebar = $this->ranking->height() > $this->pdf->availableLines() || $this->pdf->PageNo() > 1;
    }

    // 4. Render sidebar.
    $lMargin = $this->pdf->GetPageWidth() - $this->pdf->GetRightMargin() - self::SIDEBAR_WIDTH;
    [$sidebarPage, $sidebarY] = $this->pdf->with(
      [
        'lMargin' => $lMargin,
        'x' => $lMargin,
        'y' => $yPairingList,
        'page' => 1,
      ],
      function () use ($rankingInSidebar) {
        if ($rankingInSidebar) {
          $this->ranking->deleteResultColumns();
          $this->ranking->renderFitting($this->pdf);
          $this->pdf->Ln();
        }
        $this->renderInfos();
        return [$this->pdf->PageNo(), $this->pdf->GetY()];
      }
    );

    // 5. Render ranking below both pairing list if not yet rendered.
    if ($this->ranking && !$rankingInSidebar) {
      // Make sure ranking doesn't overlap with sidebar.
      $wideRanking = $this->ranking->width() > $this->pdf->availableWidth() - self::SIDEBAR_WIDTH;
      if ($wideRanking && [$sidebarPage, $sidebarY] > [$this->pdf->PageNo(), $this->pdf->GetY()]) {
        $this->ranking->deleteResultColumns();
      }
      $this->ranking->render($this->pdf);
    }
  }
  
  private function renderHeader() {
    $cell = new Cell();
    $cell->text = $this->division->league->name;
    $cell->fontSize = 12;
    $cell->height = (float) $cell->fontSize / Pdf::DEFAULT_FONT_SIZE;
    $cell->align = 'C';
    $this->pdf->render($cell);

    $cell = new Cell();
    $cell->text = $this->division->name;
    $cell->fontSize = 16;
    $cell->height = (float) $cell->fontSize / Pdf::DEFAULT_FONT_SIZE;
    $cell->align = 'C';
    $cell->fontStyle = 'B';
    $this->pdf->render($cell);

    $text = $this->matchDay->round . '. Spieltag';
    if ($this->matchDay->date) {
      // TODO: emdash (requires unicode?)
      $text .= ' - ' . date('d.m.Y', date_create($this->matchDay->date)->getTimestamp());
    }
    $cell = new Cell();
    $cell->text = $text;
    $cell->fontSize = 12;
    $cell->height = (float) $cell->fontSize / Pdf::DEFAULT_FONT_SIZE;
    $cell->align = 'C';
    $this->pdf->render($cell);

    $this->pdf->Ln();  // TODO: padding
  }

  private function renderInfos() {
    $this->renderComments();
    $this->renderLateRegistrations();
    $this->renderNextMatchDayPreview();
  }

  private function renderComments() {
    if (isset($this->matchDay->comment) && $this->matchDay->comment) {
      $this->pdf->render(new Cell('Anmerkungen', 'B'));
      $this->pdf->render(new Text($this->matchDay->comment));
      $this->pdf->Ln();
    }
    if (count($this->pairingList->remarks()) > 0) {
      foreach ($this->pairingList->remarks() as $remark) {
        $this->pdf->render($remark);
      }
      $this->pdf->Ln();
    }
  }

  private function renderLateRegistrations() {
    if (isset($this->matchDay->lateRegisteredPlayers) && $this->matchDay->lateRegisteredPlayers) {
      foreach ($this->matchDay->lateRegisteredPlayers as $teamId => $players) {
        $team = $this->division->league->teamById($teamId);
        $this->pdf->render(new Cell('Nachmeldungen ' . $team->name, 'B'));
        foreach ($players as $player) {
          $text = $player->number . ' ' . $player->name;
          if ($player->lateRegistrationRound !== $this->matchDay->round) {
            $text .= ' (ab ' . $player->lateRegistrationRound . '. Spieltag)';
          }
          $this->pdf->render(new Text($text));
        }
        $this->pdf->Ln();
      }
    }
  }

  private function renderNextMatchDayPreview() {
    if ($this->matchDay->nextMatchDay()) {
      $matchDay = $this->matchDay->nextMatchDay();
      $text = Encoding::utf8_decode('Nächster Spieltag');
      if ($matchDay->date) {
        // TODO: Add a date format method
        $text .= ' (' . date('d.m.Y', date_create($matchDay->date)->getTimestamp()) . ')';
      }
      $this->pdf->render(new Cell($text, 'B'));

      foreach ($matchDay->pairings as $pairing) {
        $text = $pairing->team1->name . ' - ' . $pairing->team2->name;
        if ($pairing->wasMoved) {
          $text .= ' (' . date('d.m.Y', date_create($pairing->moveDate)->getTimestamp()) . ')';
        }
        $this->pdf->render(new Text($text));
      }
      $this->pdf->Ln();
    }
  }

  public function getResponse() {
    return $this->pdf->asResponse('Hello.pdf', Encoding::UNICODE_ENABLED);
  }
}
