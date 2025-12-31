<?php

namespace Nsv\Util\Pdf;

/**
 * A row of Cells. The Y position will automatically be set to the next row / line
 * after rendering.
 */
class Row implements Element {

  private $cells = [];

  public function addCell(Cell $cell) {
    $this->cells[] = $cell;
  }

  /**
   * Sets the height of all cells in the row.
   */
  public function setHeight(float $height) {
    foreach ($this->cells as $cell) {
      $cell->height = $height;
    }
  }

  /**
   * Cells with no width set will be assigned a width such that
   * all available horizontal space is distributed equally to the cells.
   */
  public function layout(Pdf $pdf) {
    $availableWidth = $pdf->w - $pdf->rMargin - $pdf->x;
    $cellsToGrow = [];
    foreach ($this->cells as $cell) {
      if ($cell->width) {
        $availableWidth -= $cell->width;
      } else {
        $cellsToGrow[] = $cell;
      }
    }

    if (count($cellsToGrow)) {
      $width = (float) $availableWidth / count($cellsToGrow);
      foreach ($cellsToGrow as $cell) {
        $cell->width = $width;
      }
    }
  }

  public function render(Pdf $pdf) {
    $y = $pdf->y;
    $maxY = $y;
    foreach ($this->cells as $cell) {
      $cell->render($pdf);
      $maxY = max($pdf->y, $maxY);
      $pdf->x += $cell->width;
      $pdf->y = $y;
    }
    $pdf->SetXY($pdf->lMargin, $maxY);
  }
}
