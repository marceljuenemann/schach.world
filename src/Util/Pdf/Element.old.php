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
   * Font style. B for bold, I for italic, U for underlined.
   */
  public string $fontStyle = '';

  public function __construct(protected Pdf $pdf) {}

  /**
   * Layout the element in preparation for rendering. This function
   * should be called by the parent element and may contain the
   * following elements:
   * 
   * - minWidth: the width that should be available for rendering
   *     the element properly
   */
  // TODO: Do we want to keep this?
  public function layout(): array {
    return [];
  }

  /**
   * Render the element onto the PDF with the given width. The element
   * should render at the current X and Y position and render within
   * the current margins, which are informed by the desired width.
   */
  // TODO: position should be set to the beginning of the  next row
  public abstract function render();

  /**
   * Executes the callback with style changes (font size etc.) applied.
   */
  protected function withStyles(callable $callback): mixed {
    return $this->pdf->withFont('', $this->fontStyle, $this->fontSize, $callback);
  }
}
