<?php

namespace Nsv\Util\Pdf;

const DEFAULT_FILL_COLOR = [222, 222, 222];
const MIN_FONT_SIZE = 6;

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
   * The height of the cell relative to the line height.
   */
  public float $height = 1.0;

  /**
   * The width of the cell. If set to zero, all available horizontal space will be used.
   */
  public float $width = 0;

  /**
   * Whether to shrink the font size until the text fits into the cell.
   */
  public bool $fitText = false;

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
  public bool | array $fill = false;

  public function __construct(string $text = '', string $style = '' ) {
    $this->text = $text;
    $this->fontStyle = $style;
  }

  public function render(Pdf $pdf) {
    for ($fontSize = $this->fontSize ?? $pdf->fontSize(); $fontSize >= MIN_FONT_SIZE; $fontSize--) {
      $skipIfTooLarge = $this->fitText && $fontSize > MIN_FONT_SIZE;
      if ($pdf->withFont(null, $this->fontStyle, $fontSize, function () use ($pdf, $skipIfTooLarge) {
        if ($skipIfTooLarge && $pdf->GetStringWidth($this->text) > $this->width) {
          return false;
        }
        if ($this->fill === true) {
          $this->fill = DEFAULT_FILL_COLOR;
        }
        if (is_array($this->fill)) {
          [$r, $g, $b] = $this->fill;
          $pdf->SetFillColor($r, $g, $b);
        }
        $height = $this->height * $pdf->lineHeight;
        $pdf->Cell($this->width, $height, $this->text, $this->border,
          $height, $this->align, (bool) $this->fill, $this->link);
        return true;
      })) {
        break;
      }
    }
  }
}
