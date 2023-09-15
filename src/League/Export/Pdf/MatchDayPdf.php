<?php

namespace Nsv\League\Export\Pdf;

use Nsv\League\Core\Encoding;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Text;

class MatchDayPdf {

  public function getResponse() {
    $pdf = new Pdf();

    (new Text('Hello World!'))->render($pdf);
    (new Text('Hello Country!'))->render($pdf);

    $x = "Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.
Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.
    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.
    ";

    (new Text($x))->render($pdf);


    return $pdf->asResponse('Hello.pdf', Encoding::UNICODE_ENABLED);
  }
}
