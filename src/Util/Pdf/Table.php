<?php

namespace Nsv\Util\Pdf;

/**
 * Table that calculates column widths based on content width.
 * 
 * TODO: bold
 * TODO: thicker border
 */
class Table extends Element {

  /**
   * Widths of columns by column index. These are calculated
   * by layout() based on the content and may be modified
   * before calling render().
   */
  public array $columnWidths;

  /**
   * Cell padding left and right of the content.
   */
  public float $padding = 3.0;

  private array $rows = [];

  // TODO: verify TableCell
  public function addRow(array $cells) {
    $this->rows[] = $cells;
  }

  public function rowCount() {
    return count($this->rows);
  }
  
  public function layout(): array {
    foreach ($this->rows as $row) {
      $column = 0;
      foreach ($row as $cell) {
        $layout = $cell->content->layout();
        if (isset($layout['minWidth'])) {
          $w = $layout['minWidth'] + $this->padding;
          if (!isset($this->columnWidths[$column]) || $w > $this->columnWidths[$column]) {
            $this->columnWidths[$column] = $w;
          }
        }
        $column += $cell->colspan;
      }
    }
    // TODO: Return minWidth
    return [];
  }

  public function render() {
    $this->withStyles(function() {
      foreach ($this->rows as $row) {
        $this->renderRow($row);
      }
    });
  }

  private function renderRow(array $row) {
    $prevL = $this->pdf->GetLeftMargin();
    $prevR = $this->pdf->GetRightMargin();
    $prevY = $this->pdf->GetY();

    $maxY = $prevY;  // Keep track of the heighest column.
    $column = 0;  

    foreach ($row as $cell) {
      $width = $this->columnWidths[$column] ?: 0;
      if (!$width) continue;

      // Use margins to restrict rendering to desired width.
      $this->pdf->SetLeftMargin($this->pdf->GetX());
      $this->pdf->SetRightMargin($this->pdf->pageWidth() - ($this->pdf->GetX() + $width));
      $cell->content->render();

      // Update $maxY to get correct table height.
      if ($this->pdf->GetY() > $maxY) $maxY = $this->pdf->GetY();
      $this->pdf->SetXY($this->pdf->GetLeftMargin() + $width, $prevY);
      $column++;  // TODO: colspan
    }

    // Reset for next row.
    $this->pdf->SetLeftMargin($prevL);
    $this->pdf->SetRightMargin($prevR);
    $this->pdf->SetXY($prevL, $maxY /* TODO: border + .2 */ );
  }
}
