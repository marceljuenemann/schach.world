<?php

namespace Nsv\League\Export\Pdf;

use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\Element;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Row;
use Nsv\Util\Pdf\Table;

class Ranking implements Element {
  private const FILL_COLOURS = [
    "aufsteiger" => [197, 255, 161],
    "absteiger" => [255, 209, 207],
    "aufsteigerRelegation" => [255, 248, 171],
    "absteigerRelegation" => [255, 233, 184]
  ];

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
        $row->setCellHeight(1.1);
        $table->addRow($row);
        $first = false;
      } else {
        $row = Ranking::createTableRow(array_slice($rowData, 0, -1));
        $colour = $rowData[count($rowData) - 1];
        $row->cell(0)->align = 'R';
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
   * Sets the widths for all cells.
   */
  public function layout(Pdf $pdf) {
    $this->table->cellPadding = 3;
    $this->table->layout($pdf);

    // Set all result columns to same width.
    $maxWidth = 0;
    for ($col = 2; $col < $this->table->columnCount() - 2; $col++) {
      $maxWidth = max($maxWidth, $this->table->columnWidth($col));
    }
    for ($col = 2; $col < $this->table->columnCount() - 2; $col++) {
      $this->table->setColumnWidth($col, $maxWidth);
    }
  }

  public function width(): float {
    return $this->table->width();
  }

  public function height(): float {
    return $this->table->height();
  }

  public function deleteResultColumns() {
    $table = new Table();
    foreach ($this->table as $row) {
      $newRow = new Row();
      $newRow->addCell($row->cell(0));
      $newRow->addCell($row->cell(1));
      $newRow->addCell($row->cell($row->length() - 2));
      $newRow->addCell($row->cell($row->length() - 1));
      $newRow->marginBottom = $row->marginBottom;
      $table->addRow($newRow);
    }
    $table->setColumnSpacing(1, 0);
    $this->table = $table;
  }

  public function render(Pdf $pdf) {
    $pdf->render($this->table);
  }

  /**
   * Renders the ranking to fit the available width.
   */
  public function renderFitting(Pdf $pdf) {
    $widthChange = $pdf->availableWidth() - $this->width();
    $this->table->setColumnWidth(1, $this->table->columnWidth(1) + $widthChange);
    $this->render($pdf);
  }
}
