<?php

namespace Nsv\Util\Pdf;

/**
 * Table with dynamic column sizing.
 */
class Table {

  // TODO: Columns in constructor

  private array $rows = [];

  // TODO: addRow
  public function addRows(array $rows) {
    // TODO: check col span
    $this->rows = array_merge($this->rows, $rows);
  }

  public function rowCount() {
    return count($this->rows);
  }
  
  public function render(Pdf $pdf, array $columnWidths) {
    foreach ($this->rows as $row) {
      $row->render($pdf, $columnWidths);
    }
  }

  public function desiredColumnWidth(Pdf $pdf, int $column) {
    $minWidth = 0;
    foreach ($this->rows as $row) {
      $element = $row->cells[$column];
      $layout = $element->layout($pdf);
      if (isset($layout['minWidth']) && $layout['minWidth'] > $minWidth) {
        $minWidth = $layout['minWidth'];
      }
    }
    return $minWidth;
  }
}
