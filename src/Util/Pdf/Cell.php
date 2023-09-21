<?php

namespace Nsv\Util\Pdf;

/**
 * Cells are the basic building blocks for PDFs.
 */
class Cell extends Element {

  public function __construct(Pdf $pdf) {
    parent::__construct($pdf);
  }

  public string $text = '';
  public string $link = '';

  /**
   * The height of the cell. If null, the lineHeight of Pdf will be used. 
   */
  // TODO: relative?
  public ?float $height = null;

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

  public function layout(): array {
    $width = $this->withStyles(function() {
      return $this->pdf->GetStringWidth($this->text);
    });
    return ['minWidth' => $width];
  }

  public function render() {
    $this->withStyles(function () {
      $width = 0;  // always stretch until rMargin.
      $ln = 0;     // always use Ln() for line breaks.
      $lh = $this->height ?: $this->pdf->lineHeight;
      $this->pdf->Cell($width, $lh, $this->text, $this->border,
        $ln, $this->align, $this->fill, $this->link);
      $this->pdf->Ln();
    });
  }
}
