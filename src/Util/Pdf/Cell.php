<?php

namespace Nsv\Util\Pdf;

/**
 * Cells are the basic building blocks for PDFs.
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
   * Text alignment. Set to 'C' or 'R' to align the text in the center or to the right. 
   */
  public string $align = '';

  /**
   * Whether to fill the background of the cell.
   */
  public bool $fill = false;  // TODO: optional color

  public function render(Pdf $pdf) {
    $pdf->withFont(null, $this->fontStyle, $this->fontSize, function () use ($pdf) {
      $lh = $this->height ?: $pdf->lineHeight;
      $pdf->Cell($this->width, $lh, $this->text, $this->border,
        $lh, $this->align, $this->fill, $this->link);
    });
  }
}
