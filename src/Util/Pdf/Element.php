<?php

namespace Nsv\Util\Pdf;

/**
 * Abstraction for elements (comparable to DOM elements) to be rendered in the PDF.
 */
abstract class Element {

  /**
   * Font size in pt if different from the parent font size.
   */
  public ?int $fontSize = null;

  /**
   * Returns the desired width of the element, or null if it
   * should take as much space as possible.
   */
  // TODO: for cells only?
  // public abstract function desiredWidth(Pdf $pdf): float|null;

  /**
   * Render the element onto the PDF with the given width. The element
   * should render at the current X and Y position and render within
   * the current margins, which are informed by the desired width.
   */
  // TODO: update doc to say it has styles applied.
  // TODO: find a better name, it's really confusing. Or probaby go back to calling withStyles manually.
  // TODO: position should be set to the beginning of the  next row
  protected abstract function renderWithStyles(Pdf $pdf);

  public function render(Pdf $pdf) {
    $this->withStyles($pdf, function() use ($pdf) {
      $this->renderWithStyles($pdf);
    });
  }

  /**
   * Executes the callback with style changes (font size etc.) applied.
   */
  protected function withStyles(Pdf $pdf, callable $callback) {
    $this->fontSize ? $pdf->withFontSize($this->fontSize, $callback) : $callback();
  }
}
