<?php

namespace Nsv\Util\Pdf;

/**
 * Table is a collection of Row elements in which all columns are
 * set to the same width.
 */
class Table implements Element {

  private array $rows = [];

  public int $cellPadding = 0;

  public function addRow(Row $row) {
    if (!empty($this->rows)) {
      assert($row->length() === $this->columnCount(), 'All rows in a Table must have the same number of cells.');
    }
    $this->rows[] = $row;
  }

  public function row(int $index): Row {
    return $this->rows[$index];
  }

  public function columnCount(): int {
    assert(!empty($this->rows), 'Table has no rows.');
    return $this->rows[0]->length();
  }

  /**
   * Adds a margin to the right of the given column.
   */
  public function setColumnSpacing(int $column, float $spacing) {
    foreach ($this->rows as $row) {
      $row->cell($column)->marginRight = $spacing;
    }
  }

  /**
   * Calculates and sets the width of all columns. All cells that don't have a
   * width set will be treated to have the width that is required to fit its
   * text. 
   */
  public function layout(Pdf $pdf) {
    for ($col = 0; $col < $this->columnCount(); $col++) {
      $colWidth = 1;  // Ensure non-zero width for all columns.
      foreach ($this->rows as $row) {
        $cell = $row->cell($col);
        $width = $cell->width;
        if (!$width) {
          // TODO: This does not take font size changes into account.
          $width = $pdf->GetStringWidth($cell->text) + $this->cellPadding;
        }
        $colWidth = max($colWidth, $width);
      }
      // Now actually set the width for all cells in this column.
      foreach ($this->rows as $row) {
        $row->cell($col)->width = $colWidth;
      }
    }
  }

  public function render(Pdf $pdf) {
    foreach ($this->rows as $row) {
      $row->render($pdf);
    }
  }
}
