<?php

namespace Nsv\Util\Pdf;

/**
 * Table is a collection of Row elements in which all columns are
 * set to the same width.
 */
class Table implements Element {

  private array $rows = [];

  public function addRow(Row $row) {
    // TODO: assert same number of columns
    $this->rows[] = $row;
  }

  /**
   * Calculates and sets the width of all columns. All cells that don't have a
   * width set will be treated to have the width that is required to fit its
   * text. 
   */
  public function layout(Pdf $pdf) {
    foreach ($this->rows as $row) {
      $column = 0;
      foreach ($row as $cell) {
        if (!$cell->width) {
          // TODO: This does not take font size changes into account.
          $minWidth = $pdf->GetStringWidth($cell->text);
          $cell->width = $minWidth;
        }
      
        /*
        $layout = $cell->content->layout();
        if ($cell->colspan == 1 && isset($layout['minWidth'])) {
          $w = $layout['minWidth'] + $this->padding;
          if (!isset($this->columnWidths[$column]) || $w > $this->columnWidths[$column]) {
            $this->columnWidths[$column] = $w;
          }
        }
        $column += $cell->colspan;
        */
      }
    }
  }

  public function render(Pdf $pdf) {
    foreach ($this->rows as $row) {
      $row->render($pdf);
    }
  }
}
