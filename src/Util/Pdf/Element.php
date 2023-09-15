<?php

namespace Nsv\Util\Pdf;

/**
 * Abstraction for elements (comparable to DOM elements) to be rendered in the PDF.
 */
abstract class Element {

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
  // TODO: if this is it, then it's not needed :)
  public abstract function render(Pdf $pdf);

}
