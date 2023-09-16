<?php

namespace Nsv\Util\Pdf;

/**
 * Table with dynamic column sizing.
 */
class Table extends Element {

  // TODO: Columns in constructor

  private array $rows = [];

  public function addRows(array $rows) {
    // TODO: check col span
    $this->rows = array_merge($this->rows, $rows);
  }

  private function calculateColumnWidths() {
    return [50, 50];
  }
  
  protected function renderWithStyles(Pdf $pdf) {
    $columnWidths = $this->calculateColumnWidths();
    foreach ($this->rows as $row) {
      $row->render($pdf, $columnWidths);
    }
  }
}
