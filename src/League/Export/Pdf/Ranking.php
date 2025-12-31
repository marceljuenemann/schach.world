<?php

namespace Nsv\League\Export\Pdf;

use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\Element;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Row;
use Nsv\Util\Pdf\Table;
use Nsv\Util\Pdf\TableCell;
use Nsv\Util\Pdf\TableRow;

/**
 * Ranking pdf element.
 * 
 * TODO: fill color
 * TODO: bold headings
 * TODO: equal column widths for results
 * TODO: thick border
 * TODO: decide whether to render full table dynamically
 */
class Ranking implements Element {

  private Table $table;

  public function __construct(array $legacyRanking) {
    $this->table = Ranking::createTable($legacyRanking);
  }

  private static function createTable(array $legacyRanking) {
    $table = new Table();
    foreach ($legacyRanking as $row) {
      $table->addRow(Ranking::createTableRow($row));
    }
    return $table;
  }

  private static function createTableRow(array $cells): Row {
    $row = new Row();
    foreach ($cells as $cellData) {
      if (is_array($cellData)) {
        if (isset($cellData['text'])) {
          $text = str_replace('xxx', '', $cellData['text']);
        } else {
          $text = implode(', ', array_map(function ($data) {
            return $data['text'];
          }, $cellData));
        }
      } else {
        $text = str_replace('xxx', '', $cellData);
      }
      $cell = new Cell();
      $cell->text = $text;
      $cell->border = 1;
      $cell->align = 'C';
      $row->addCell($cell);
    }
    return $row;
  }

  /**
   * Calculates widths for all cells.
   */
  public function layout(Pdf $pdf) {
    $this->table->layout($pdf);
    /*
    $teamCount = $this->table->rowCount() - 1;

    // Calculate column widths.
    $padding = $this->pdf->GetStringWidth('      ');
    $place = $this->pdf->GetStringWidth($this->table->rowCount());
    $team = $this->table->desiredColumnWidth($this->pdf, 1);
    $points = $this->table->desiredColumnWidth($this->pdf, $teamCount + 3);

    // Use same width for all result columns.
    $resultWdith = $place;
    for ($i = 1; $i < $this->table->rowCount(); $i++) {
      $colWidth = $this->table->desiredColumnWidth($this->pdf, 1 + $i);
      if ($colWidth > $resultWdith) $resultWdith = $colWidth;
    }
    $resultWdiths = array_fill(0, $this->table->rowCount() - 1, $resultWdith + $padding);


    $widths = array_merge([
      $place + $padding,
      $team + $padding
    ], $resultWdiths, [
      $points + $padding,
      $points + $padding, 0]);

    $this->columnWidths = $widths;  // TODO: move to Table.
    return [];
    $this->table->layout();
    */
  }

  public function render(Pdf $pdf) {
    $pdf->render($this->table);
  }
}
