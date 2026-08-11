<?php

namespace Nsv\Util\Pdf;

use Fpdf\Fpdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simple wrapper around FPDF to gernerate simple PDFs.
 */
class Pdf extends Fpdf {
  const DEFAULT_FONT_SIZE = 9;

  public float $lineHeight;
  public int $lastPage = 1;

  public function __construct() {
    parent::__construct();
    $this->tMargin = 8;
    $this->bMargin = 8;
    $this->AddPage();
    $this->SetFont('helvetica', '', self::DEFAULT_FONT_SIZE);
    $this->lineHeight = 4;
  }

  public function GetTopMargin() {
    return $this->tMargin;
  }

  public function GetLeftMargin() {
    return $this->lMargin;
  }

  public function GetRightMargin() {
    return $this->rMargin;
  }

  /**
   * Return the available width until the right margin in user units.
   */
  public function availableWidth(): float {
    return $this->w - $this->rMargin - $this->x;
  }

  /**
   * Return the available height on the current page in user units.
   */
  public function availableHeight(): float {
    return $this->h - $this->bMargin - $this->y;
  }

  /**
   * Return the available height on the current page relative to the line height.
   */
  public function availableLines(): float {
    return $this->availableHeight() / $this->lineHeight;
  }

  public function fontSize(): float {
    return $this->FontSizePt;
  }

  /**
   * Executes the callback with the given font family and style.
   * 
   * Any null values indicate that the current value should be kept.
   */
  public function withFont(string | null $family, string | null $style, int | null $size, callable $callback): mixed {
    $prevFamily = $this->FontFamily;
    $prevStyle = $this->FontStyle;
    $prevSize = $this->FontSizePt;

    $this->SetFont($family ?: $prevFamily, $style ?: $prevStyle, $size ?: $prevSize);
    $result = $callback();
    $this->SetFont($prevFamily, $prevStyle, $prevSize);

    return $result;
  }

  /**
   * Executes the callback with a different font size.
   */
  public function withFontSize(int $fontSize, callable $callback): mixed {
    return $this->withFont(null, null, $fontSize, $callback);
  }

  /**
   * Executes the callback with the given properties set on the PDF.
   * The given properties will be restored after the callback.
   */
  public function with(array $properties, callable $callback): mixed {
    $prevValues = [];
    foreach ($properties as $property => $value) {
      $prevValues[$property] = $this->$property;
      if ($property == 'page') {
        $this->changePage($value);
      } else {
        $this->$property = $value;
      }
    }
    $result = $callback();
    foreach ($prevValues as $property => $value) {
      $this->$property = $value;
    }
    $this->lastPage = max($this->lastPage, $this->page);
    return $result;
  }

  public function changePage(int $page) {
    if ($page != $this->page) {
      assert($page <= $this->lastPage, "Tried to change to non-existing page $page.");
      $this->page = $page;
      // FPDP usually does not support changing pages. We need to force it to ouput
      // the font command again by changing fonts back and forth. 
      $fontSize = $this->FontSizePt;
      $this->SetFont($this->FontFamily, $this->FontStyle, $fontSize + 1);
      $this->SetFont($this->FontFamily, $this->FontStyle, $fontSize);
    }
  }

  public function render(Element $element) {
    $element->render($this);
    $this->lastPage = max($this->lastPage, $this->page);
  }

  public function Ln($height = null) {
    parent::Ln($height ?: $this->lineHeight);
  }
 
  public function asResponse(string $filename, bool $isUtf8 = true): Response {
    $this->page = $this->lastPage;
    $body = $this->Output('S', $filename, $isUtf8);
    return new Response($body, 200, [
      'Content-Type' => 'application/pdf',
      'Content-Disposition' => 'inline; '.$this->_httpencode('filename', $filename, $isUtf8)
    ]);
  }
}
