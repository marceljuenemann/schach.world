<?php

namespace Nsv\Util\Pdf;

// TODO: Switch back to composer version.
require_once("../ligen/_inc/extern/fpdf.php");

//use Fpdf\Fpdf;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simple wrapper around FPDF to gernerate simple PDFs.
 */
class Pdf extends \FPDF /*Fpdf*/ {

  // TODO: set title and other metadata.

  public float $lineHeight;
  public int $lastPage = 1;

  public function __construct() {
    parent::__construct();
    $this->tMargin = 8;
    $this->bMargin = 8;
    $this->AddPage();
    $this->SetFont('helvetica', '', 9);
    $this->lineHeight = 4;
  }

  public function GetLeftMargin() {
    return $this->lMargin;
  }

  public function GetRightMargin() {
    return $this->rMargin;
  }

  public function pageWidth(): float {
    return $this->w;
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

  /**
   * Executes the callback with the given font family and style.
   * 
   * Any null values indicate that the current value should be kept.
   */
  // TODO: Add a withLineHeight method?
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
   * Executes the callback with a different font size. Also adjusts the
   * line height proportionally.
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
      // Work around a FPDF issue where the font is not correctly set
      //$this->pdf->SetFont($this->pdf->FontFamily, 'B', $this->pdf->FontSizePt);
     // $this->pdf->SetFont($this->pdf->FontFamily, '', $this->pdf->FontSizePt);
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
    //$body = $this->Output('S', $filename, $isUtf8);
    $this->page = $this->lastPage;
    $body = $this->Output($filename, 'S');
    return new Response($body, 200, [
      'Content-Type' => 'application/pdf',
//      'Content-Disposition' => 'inline; '.$this->_httpencode('filename', $filename, $isUtf8)
//      'Content-Disposition' => 'inline; '.$this->_httpencode('filename', $filename, $isUtf8)
    ]);
  }
}

/*

    
    $widthOfGames = $pageWidth - 2*10 - $columnwidth - 3;
    {
        $width[0] = $options ['showPassNr']
                  ? ( $prefs['spielDreistelligeNr'] ? $pdf->GetStringWidth ( "616" ) : $pdf->GetStringWidth ( "24" ) ) + 3
                  : 0;
            // Links
            $links = array (
                $urlbase . "?mannschaft=" . $data ['paarungen'][$i]['mid1'],
                "", "", "", "", "", "", "",
                $urlbase . "?mannschaft=" . $data ['paarungen'][$i]['mid2']
            );

            // Verlegung & Ausrichter
            if ( count ( $data ['paarungen'][$i]['paarungen'] ) == 0 )
            {
                $pdf->SetFont ( "", "", $fontsize );
                if ( $data ['paarungen'][$i]['datum'] != $data ['datum'] )
                    $pdf->Cell ( $widthOfGames, $cellheight, "Findet statt am ".$data ['paarungen'][$i]['datum'], 1, 1, "C", 0 );
                if ( $data ['paarungen'][$i]['ausrichterId'] != $data ['paarungen'][$i]['mid1'] )
                    $pdf->Cell ( $widthOfGames, $cellheight, "Ausgerichtet von: ".$data ['paarungen'][$i]['ausrichter'], 1, 1, "C", 0 );
            }

            // kampflos gewonnen?
            elseif ( $data ['paarungen'][$i]['kampflos'] ){
                $pdf->SetFont ( "", "", $fontsize );
                $pdf->Cell ( $widthOfGames, $cellheight, "(kampflos)", 1, 1, "C", 0 );
            }


    ///////////////////////////////////
    // Fußzeile
    {
        $pdf->SetFont ( "", "", 10 );
        $pdf->SetAutoPageBreak ( false );
        $pdf->SetLeftMargin ( 10 );
        $pdf->SetY ( -15 );

        // Internetadresse ausgeben
        $text = "Internet: " . $urlbase;
        $pdf->Cell ( 0, 4.5, $text, 0, 1, "C", false, $urlbase );

        // Staffelleiter ausgeben
        $text = sprintf ( "Staffelleiter: %s - Tel.: %s - Email: %s", $data ['sl_name'], $data ['sl_telefon'], $data ['sl_email'] );
        $pdf->Cell ( 0, 4.5, $text, 0, 1, "C" );
    }

*/