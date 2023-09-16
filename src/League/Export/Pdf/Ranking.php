<?php

namespace Nsv\League\Export\Pdf;

use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\Element;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Table;
use Nsv\Util\Pdf\TableRow;

/**
 * Ranking pdf element.
 */
class Ranking extends Element {

  private Table $table;

  public function __construct(array $legacyRanking) {
    $this->table = Ranking::createTable($legacyRanking);
  }
  
  protected function renderWithStyles(Pdf $pdf) {
    $this->table->render($pdf);
  }

  private static function createTable(array $legacyRanking) {
    $table = new Table();
    $table->addRows(array_map(function ($row) {
      return Ranking::createTableRow($row);
    }, $legacyRanking));
    return $table;
  }

  private static function createTableRow(array $row) {
    return new TableRow(array_map(function ($data) {
      $text = is_array($data) ? (isset($data['text']) ? $data['text'] : 'erg') : $data;
      $cell = new Cell($text);
      $cell->border = 1;
      $cell->align = 'C';
      return $cell;
    }, $row));
  }
}
