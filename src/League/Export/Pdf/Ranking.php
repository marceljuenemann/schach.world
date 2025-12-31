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
 * TODO: equal column widths for results
 */
class Ranking implements Element {
  private const FILL_COLOURS = [
    "aufsteiger" => [197, 255, 161],
    "absteiger" => [255, 209, 207],
    "aufsteigerRelegation" => [255, 248, 171],
    "absteigerRelegation" => [255, 233, 184]
  ];

  private Row $header;
  private Table $table;

  public function __construct(array $legacyRanking) {
    $this->table = Ranking::createTable($legacyRanking);
  }

  private static function createTable(array $legacyRanking) {
    $table = new Table();
    $first = true;
    foreach ($legacyRanking as $rowData) {
      if ($first) {
        $row = Ranking::createTableRow($rowData);
        $row->setFill(true);
        $row->setStyle('B');
        $row->marginBottom = .2;  // Slightly thicker border below header.
        $row->cell(1)->align = 'C';  // Team name left aligned.
        $table->addRow($row);
        $first = false;
      } else {
        $row = Ranking::createTableRow(array_slice($rowData, 0, -1));
        $colour = $rowData[count($rowData) - 1];
        if (isset(Ranking::FILL_COLOURS[$colour])) {
          $row->cell(0)->fill = Ranking::FILL_COLOURS[$colour];
        }
        $table->addRow($row);
      }
    }
    // Add slightly thicker border.
    $table->setColumnSpacing(1, .2);
    $table->setColumnSpacing($table->columnCount() - 3, .2);
    return $table;
  }

  private static function createTableRow(array $cells): Row {
    $row = new Row();
    foreach ($cells as $cellData) {
      $cell = new Cell();
      if (is_array($cellData)) {
        if (isset($cellData['text'])) {
          $cell->text = $cellData['text'];
        } else {
          $cell->text = implode(', ', array_map(function ($data) {
            return $data['text'];
          }, $cellData));
        }
      } else if ($cellData === 'xxx') {
        $cell->fill = true;
      } else {
        $cell->text = $cellData;
      }
      $cell->border = 1;
      $cell->align = 'C';
      $row->addCell($cell);
    }
    $row->cell(1)->align = 'L';  // Team name left aligned.
    return $row;
  }

  /**
   * Calculates widths for all cells.
   */
  public function layout(Pdf $pdf) {
    $this->header = $this->table->row(0);
    $this->header->setHeight($pdf->lineHeight * 1.1);

    $this->table->cellPadding = 3;
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
