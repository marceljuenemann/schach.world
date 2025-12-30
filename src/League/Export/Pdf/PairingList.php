<?php

namespace Nsv\League\Export\Pdf;

use Nsv\League\Api\Model\Game;
use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Api\Model\Pairing;
use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\Element;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Row;
use Nsv\Util\Pdf\Table;
use Nsv\Util\Pdf\TableCell;

/**
 * PDF element displaying pairings.
 * 
 * TODO: Link
 * TODO: Background
 * TODO: Bold
 * TODO: Expand
 * TODO: kampflos
 * TODO: verlegt
 * TODO: pass nr
 * TODO: pass nr based on setting
 * TODO: resize columns if needed
 */
class PairingList implements Element {

  public function __construct(private MatchDay $matchDay) {}

  /*
  private Table $table;

  public function __construct(Pdf $pdf, private MatchDay $matchDay) {
    $this->table = new Table($pdf);
    foreach ($matchDay->pairings as $pairing) {
      $this->addPairing($pairing);
    }
  }

  private function addPairing(Pairing $pairing) {
    $this->table->addRow($this->pairingHeader($pairing));
    if (isset($pairing->games)) {
      foreach ($pairing->games as $game) {
        $this->table->addRow($this->gameRow($game));
      }
    }
    $this->table->addRow($this->emptyRow());
  }
    */

  public function render(Pdf $pdf) {
    foreach ($this->matchDay->pairings as $pairing) {
      $this->renderHeader($pdf, $pairing);
    }
  }

  private function renderHeader(Pdf $pdf, Pairing $pairing) {
    // TODO: Handle comments

    $row = new Row();

    $cell = new Cell();
    $cell->text = $pairing->team1->name;
    $cell->border = 'LTB';
    $cell->fontStyle = 'B';
    $cell->fill = 1;
    // TODO: URI
    $row->addCell($cell);
    
    $cell = new Cell();
    $cell->text = $pairing->result ?: ':';
    $cell->border = 'TB';
    $cell->align = 'C';
    $cell->fontStyle = 'B';
 //   $cell->fill = 1;
    // TODO: URI
    $row->addCell($cell);
 
    $cell = new Cell();
    $cell->text = $pairing->team2->name;
    $cell->border = 'TBR';
    $cell->align = 'R';
    $cell->fontStyle = 'B';
//    $cell->fill = 1;
    // TODO: URI
    $row->addCell($cell);

    $row->layout($pdf);
    $pdf->render($row);
    $pdf->Ln();
  }

    /*

  private function gameRow(Game $game): array {
    // Player number.
    $cell = new Cell($this->pdf);
    $cell->text = $game->player1 ? $game->player1->number : '';
    $cell->border = 1;
    $cell->align = 'C';
    $row[] = new TableCell($cell);

    // Player name.
    $cell = new Cell($this->pdf);
    $cell->text = $game->player1 ? $game->player1->name : '';
    $cell->border = 'LTB';
    // TODO: URI
    $row[] = new TableCell($cell);

    // Player rating.
    $cell = new Cell($this->pdf);
    $cell->text = $game->player1 && $game->player1->dwz ? "({$game->player1->dwz})" : '';
    $cell->border = 'TBR';
    $cell->align = 'R';
    $cell->fontSize = 8;
    // TODO: Don't automatically scale with font
    $cell->height = $this->pdf->lineHeight;
    $row[] = new TableCell($cell);

    // Result.
    $cell = new Cell($this->pdf);
    $cell->text = $game->result1 . ' : ' . $game->result2;
    $cell->border = 'LTBR';
    $cell->align = 'C';
    $row[] = new TableCell($cell);

    // Player rating.
    $cell = new Cell($this->pdf);
    $cell->text = $game->player2 && $game->player2->dwz ? "({$game->player2->dwz})" : '';
    $cell->border = 'LTB';
    $cell->align = 'L';
    $cell->fontSize = 8;
    // TODO: Don't automatically scale with font
    $cell->height = $this->pdf->lineHeight;
    $row[] = new TableCell($cell);
    
    // Player name.
    $cell = new Cell($this->pdf);
    $cell->text = $game->player2 ? $game->player2->name : '';
    $cell->border = 'TBR';
    $cell->align = 'R';
    // TODO: URI
    $row[] = new TableCell($cell);

    // Player number.
    $cell = new Cell($this->pdf);
    $cell->text = $game->player2 ? $game->player2->number : '';
    $cell->border = 1;
    $cell->align = 'C';
    $row[] = new TableCell($cell);
    
    return $row;
  }


  private function emptyRow(): array {
    return [new TableCell(new Cell($this->pdf))];
  }

  public function layout(): array {
    $this->table->layout();
    return [];
  }

  public function render() {
    $this->withStyles(function() {
      $this->table->render();
    });
  }
  */
}
