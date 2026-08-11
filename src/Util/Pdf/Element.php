<?php

namespace Nsv\Util\Pdf;

/**
 * An element that can be rendered to a PDF.
 */
interface Element {

  function render(Pdf $pdf);

}
