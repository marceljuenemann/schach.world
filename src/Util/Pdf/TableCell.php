<?php

namespace Nsv\Util\Pdf;

class TableCell {

  /**
   * Number of columns that this cell spans.
   */
  public int $colspan = 1;

  public function __construct(public Element $content) {}

}
