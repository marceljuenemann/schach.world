<?php

namespace Nsv\Util\Pdf;

class TableCell {

  public function __construct(
    public Element $content,
    public int $colspan = 1
  ) {}

}
