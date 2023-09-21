<?php

namespace Nsv\League\Export\Pdf;

use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\Element;
use Nsv\Util\Pdf\Pdf;
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
class Ranking extends Element {

  private Table $table;

  public function __construct(Pdf $pdf, array $legacyRanking) {
    parent::__construct($pdf);
    $this->table = Ranking::createTable($pdf, $legacyRanking);
  }
  
  public function layout(): array {
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
    */
    $this->table->layout();
    return [];
  }

  public function render() {
    $this->withStyles(function() {
      $this->table->render();
    });
  }

  private static function createTable(Pdf $pdf, array $legacyRanking) {
    $table = new Table($pdf);
    foreach ($legacyRanking as $row) {
      $table->addRow(Ranking::createTableRow($pdf, $row));
    }
    return $table;
  }

  private static function createTableRow(Pdf $pdf, array $row): array {
    return array_map(function ($data) use ($pdf) {
      if (is_array($data)) {
        if (isset($data['text'])) {
          $text = str_replace('xxx', '', $data['text']);
        } else {
          $text = implode(', ', array_map(function ($data) {
            return $data['text'];
          }, $data));
        }
      } else {
        $text = str_replace('xxx', '', $data);
      }
      $cell = new Cell($pdf);
      $cell->text = $text;
      $cell->border = 1;
      $cell->align = 'C';
      return new TableCell($cell);
    }, $row);
  }
}
