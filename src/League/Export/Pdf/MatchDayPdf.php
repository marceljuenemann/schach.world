<?php

namespace Nsv\League\Export\Pdf;

use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\LineBreak;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Table;
use Nsv\Util\Pdf\TableRow;
use Nsv\Util\Pdf\Text;

class MatchDayPdf {

  private Pdf $pdf;
  private PairingList $pairingList;
  private Ranking $ranking;

  public function __construct(private Division $division, private MatchDay $matchDay) {
    $this->pdf = new Pdf();
    $this->pairingList = new PairingList($this->pdf, $matchDay);
    $this->ranking = new Ranking($this->pdf, $matchDay->legacyRanking);
  }

  public function render() {
    $this->renderHeader();

    $this->pairingList->layout();
    $this->ranking->layout();

    $this->pairingList->render();
    $this->ranking->render();
  }
  
  private function renderHeader() {
    $cell = new Cell($this->pdf);
    $cell->text = $this->division->league->name;
    $cell->fontSize = 12;
    $cell->align = 'C';
    $cell->render();

    // TODO: bold
    $cell = new Cell($this->pdf);
    $cell->text = $this->division->name;
    $cell->fontSize = 16;
    $cell->align = 'C';
    $cell->render();

    $text = $this->matchDay->round . '. Spieltag';
    if ($this->matchDay->date) {
      $text .= ' - ' . date('d.m.Y', date_create($this->matchDay->date)->getTimestamp());
    }
    $cell = new Cell($this->pdf);
    $cell->text = $text;
    $cell->fontSize = 12;
    $cell->align = 'C';
    $cell->render($this->pdf);

    $this->pdf->Ln();  // TODO: padding
  }

  public function getResponse() {
    return $this->pdf->asResponse('Hello.pdf', Encoding::UNICODE_ENABLED);
  }
}
