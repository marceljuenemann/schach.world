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
 * TODO: kampflos
 * TODO: verlegt
 * TODO: pass nr based on setting
 * TODO: resize columns if needed
 */
class PairingList implements Element {

  const WIDTH_PLAYER_NUMBER = 10;
  const WIDTH_PLAYER_RATING = 15;
  const WIDTH_RESULT = 13;

  public function __construct(private MatchDay $matchDay) {}

  public function render(Pdf $pdf) {
    foreach ($this->matchDay->pairings as $pairing) {
      $this->renderHeader($pdf, $pairing);
      if (isset($pairing->games)) {
        foreach ($pairing->games as $game) {
          $this->renderGame($pdf, $game);
        }
      }
      $pdf->Ln();
    }
  }

  private function renderHeader(Pdf $pdf, Pairing $pairing) {
    // TODO: Handle comments
    // TODO: Maybe slightly larger line height

    $row = new Row();

    $cell = new Cell();
    $cell->text = $pairing->team1->name;
    $cell->border = 'LTB';
    $cell->fontStyle = 'B';
    $cell->fill = true;
    // TODO: URI
    $row->addCell($cell);
    
    $cell = new Cell();
    $cell->text = $pairing->result ?: ':';
    $cell->border = 'TB';
    $cell->align = 'C';
    $cell->fontStyle = 'B';
    $cell->fill = true;
    $cell->width = self::WIDTH_RESULT * 2;  // Headline may take up some more space.
    // TODO: URI
    $row->addCell($cell);
 
    $cell = new Cell();
    $cell->text = $pairing->team2->name;
    $cell->border = 'TBR';
    $cell->align = 'R';
    $cell->fontStyle = 'B';
    $cell->fill = true;
    // TODO: URI
    $row->addCell($cell);

    $row->setHeight($pdf->lineHeight * 1.1);
    $row->layout($pdf);
    $pdf->render($row);
  }

  private function renderGame(Pdf $pdf, Game $game) {
    $row = new Row();

    // Player number.
    $cell = new Cell();
    $cell->text = $game->player1 ? $game->player1->number : '';
    $cell->border = 1;
    $cell->align = 'C';
    $cell->width = self::WIDTH_PLAYER_NUMBER;
    $row->addCell($cell);

    // Player name.
    $cell = new Cell();
    $cell->text = $game->player1 ? $game->player1->name : '';
    $cell->border = 'LTB';
    // TODO: URI
    $row->addCell($cell);

    // Player rating.
    $cell = new Cell();
    $cell->text = $game->player1 && $game->player1->dwz ? "({$game->player1->dwz})" : '';
    $cell->border = 'TBR';
    $cell->align = 'R';
    $cell->fontSize = 8;
    $cell->fontStyle = 'I';
    $cell->width = self::WIDTH_PLAYER_RATING;
    $row->addCell($cell);

    // Result.
    $cell = new Cell();
    $cell->text = $game->result1 . ' : ' . $game->result2;
    $cell->border = 'LTBR';
    $cell->align = 'C';
    $cell->width = self::WIDTH_RESULT;
    $row->addCell($cell);

    // Player rating.
    $cell = new Cell();
    $cell->text = $game->player2 && $game->player2->dwz ? "({$game->player2->dwz})" : '';
    $cell->border = 'LTB';
    $cell->align = 'L';
    $cell->fontSize = 8;
    $cell->fontStyle = 'I';
    $cell->width = self::WIDTH_PLAYER_RATING;
    $row->addCell($cell);
    
    // Player name.
    $cell = new Cell();
    $cell->text = $game->player2 ? $game->player2->name : '';
    $cell->border = 'TBR';
    $cell->align = 'R';
    // TODO: URI
    $row->addCell($cell);

    // Player number.
    $cell = new Cell();
    $cell->text = $game->player2 ? $game->player2->number : '';
    $cell->border = 1;
    $cell->align = 'C';
    $cell->width = self::WIDTH_PLAYER_NUMBER;
    $row->addCell($cell);
    
    $row->setHeight($pdf->lineHeight);
    $row->layout($pdf);
    $pdf->render($row);
  }
}
