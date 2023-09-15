<?php

namespace Nsv\League\Export\Pdf;

use Nsv\League\Core\Encoding;
use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\LineBreak;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Text;

class MatchDayPdf {

  public function getResponse() {
    $pdf = new Pdf();

    (new Text('Hello World!'))->render($pdf);
    (new Text('Hello Country!'))->render($pdf);
    $ln = new LineBreak();

    $ln->render($pdf);

    $cell = new Cell();
    $cell->text = 'My cell';
    $cell->link = 'https://example.com';
    $cell->border = 1;
    $cell->align = 'C';
    $cell->render($pdf);
    $ln->render($pdf);

    $cell = new Cell();
    $cell->text = 'Landes- und Verbandsligen';
    $cell->fontSize = 16;
    $cell->align = 'C';
    $cell->border = 1;
    $cell->render($pdf);
    $ln->render($pdf);

    $cell = new Cell();
    $cell->text = 'Staffel 3000';
    $cell->fontSize = 19;
    $cell->align = 'C';
    $cell->border = 1;
    $cell->render($pdf);
    $ln->render($pdf);


    $x = "Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.
Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.
    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.    Lorem Ipsum dolor sit.  Lorem Ipsum dolor sit.
    ";

    (new Text($x))->render($pdf);




    return $pdf->asResponse('Hello.pdf', Encoding::UNICODE_ENABLED);
  }
}
