<?php

namespace Nsv\League\Api\Service\Pdf;

use Nsv\League\Api\Model\Game;
use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Api\Model\Pairing;
use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\Element;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Row;
use Nsv\Util\Pdf\Text;

/**
 * PDF element displaying pairings.
 * 
 * TODO: Link
 * TODO: kampflos
 * TODO: verlegt
 */
class PairingList implements Element {

  const WIDTH_PLAYER_NUMBER = 10;
  const WIDTH_PLAYER_NUMBER_SHORT = 7;
  const WIDTH_PLAYER_RATING = 15;
  const WIDTH_RESULT = 13;

  private array $remarkSymbols;
  private array $remarks = [];
  private int $widthPlayerNumber;

  public function __construct(private MatchDay $matchDay, private bool $playerNumbersWithTeamNumber) {
    $this->remarkSymbols = array ( 0, chr(0xB9), chr(0xB2), chr(0xB3), '*' );
    $this->widthPlayerNumber = $playerNumbersWithTeamNumber ? self::WIDTH_PLAYER_NUMBER : self::WIDTH_PLAYER_NUMBER_SHORT;
  }

  public function remarks(): array {
    return $this->remarks;
  }

  public function render(Pdf $pdf) {
    // TODO: Move to next page if not all games fit on this page.
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

    $row = new Row();

    $cell = new Cell();
    $cell->text = $pairing->team1->name;
    $cell->border = 'LTB';
    $cell->link = $pairing->team1->uri;
    $row->addCell($cell);
    
    $cell = new Cell();
    $cell->text = $pairing->result ?: ':';
    $cell->border = 'TB';
    $cell->align = 'C';
    $cell->width = self::WIDTH_RESULT * 2;  // Headline may take up some more space.
    $row->addCell($cell);

    if (isset($pairing->comment) && $pairing->comment) {
      $symbol = next($this->remarkSymbols) ?: end($this->remarkSymbols);
      $cell->text .= ' ' . $symbol;
      $this->remarks[] = new Text($symbol . ' ' . $pairing->comment);
    }

    $cell = new Cell();
    $cell->text = $pairing->team2->name;
    $cell->border = 'TBR';
    $cell->align = 'R';
    $cell->link = $pairing->team2->uri;
    $row->addCell($cell);

    $row->setCellHeight(1.1);
    $row->setFill(true);
    $row->setStyle('B');
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
    $cell->width = $this->widthPlayerNumber;
    $row->addCell($cell);

    // Player name.
    $cell = new Cell();
    $cell->text = $game->player1 ? $game->player1->name : '';
    $cell->border = 'LTB';
    $cell->link = $game->player1 ? $game->player1->uri : '';
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
    $cell->link = $game->player2 ? $game->player2->uri : '';
    $row->addCell($cell);

    // Player number.
    $cell = new Cell();
    $cell->text = $game->player2 ? $game->player2->number : '';
    $cell->border = 1;
    $cell->align = 'C';
    $cell->width = $this->widthPlayerNumber;
    $row->addCell($cell);
    
    $row->layout($pdf);
    $pdf->render($row);
  }
}
