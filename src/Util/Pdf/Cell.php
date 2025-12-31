<?php

namespace Nsv\Util\Pdf;

const DEFAULT_FILL_COLOR = [222, 222, 222];

/**
 * Outputs a single cell, the basic building block of PDFs.
 * 
 * The "cursor" will be set to the next row, i.e. $pdf->x will remain the same
 * after rendering, with $pdf->y being increased by the height of the cell. 
 */
class Cell implements Element {

  public string $text = '';
  public string $link = '';

  /**
   * Font size in pt if different from the parent font size.
   */
  public ?int $fontSize = null;

  /**
   * Font style. B for bold, I for italic, U for underlined.
   */
  public string $fontStyle = '';

  /**
   * The height of the cell. If null, the lineHeight of Pdf will be used. 
   */
  // TODO: relative?
  public ?float $height = null;

  /**
   * The width of the cell. If set to zero, all available horizontal space will be used.
   */
  // TODO: Support dynamic?
  // TODO: Maybe different Cell subclasses?
  public float $width = 0;

  /**
   * Which border to draw, e.g. "LR" for left and right or 1 for all borders.
   */
  public string|int $border = 0;

  /**
   * Margin to the right of the cell.
   */
  public float $marginRight = 0;

  /**
   * Text alignment. Set to 'C' or 'R' to align the text in the center or to the right. 
   */
  public string $align = '';

  /**
   * Whether to fill the background of the cell.
   * 
   * If set to true, the default fill color will be used. If set to an array,
   * it should contain RGB values.
   */
  public bool | array $fill = false;  // TODO: support RGB

  public function render(Pdf $pdf) {
    $pdf->withFont(null, $this->fontStyle, $this->fontSize, function () use ($pdf) {
      if ($this->fill === true) {
        $this->fill = DEFAULT_FILL_COLOR;
      }
      if (is_array($this->fill)) {
        [$r, $g, $b] = $this->fill;
        $pdf->SetFillColor($r, $g, $b);
      }
      $lh = $this->height ?: $pdf->lineHeight;
      $pdf->Cell($this->width, $lh, $this->text, $this->border,
        $lh, $this->align, (bool) $this->fill, $this->link);
    });
  }
}
