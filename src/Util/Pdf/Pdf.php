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

  public function __construct() {
    parent::__construct();
    $this->AddPage();
    $this->SetFont('helvetica', '', 9);
    $this->lineHeight = 4;

    /*
    $pdf->SetAutoPageBreak(true, 15);
    */
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
   * Executes the callback on the given page.
   */
  public function onPage(int $page, callable $callback): mixed {
    if ($page == $this->page) {
      return $callback();
    }
    assert($page >= 1 && $page <= $this->page, "Page out of range");
    $prevPage = $this->page;
    $this->page = $page;
    // Ensure that the correct font is active on the page.
    $this->SetFont($this->FontFamily, $this->FontStyle, $this->FontSizePt);
    $result = $callback();
    $this->page = max($prevPage, $this->page);
    return $result;
  }

  public function render(Element $element) {
    $element->render($this);
  }

  public function Ln($height = null) {
    parent::Ln($height ?: $this->lineHeight);
  }
 
  public function asResponse(string $filename, bool $isUtf8 = true): Response {
    //$body = $this->Output('S', $filename, $isUtf8);
    $body = $this->Output($filename, 'S');
    return new Response($body, 200, [
      'Content-Type' => 'application/pdf',
//      'Content-Disposition' => 'inline; '.$this->_httpencode('filename', $filename, $isUtf8)
//      'Content-Disposition' => 'inline; '.$this->_httpencode('filename', $filename, $isUtf8)
    ]);
  }
}

/*

    // Logik, damit alles auf eine Seite passt
    // tmp = (fontsize,lineheight,kreuztabelle)
    $linecount = count ( [] $data ['paarungen'] ) * ( SED_GetBrettzahl ( $_GET ['staffel'] ) + 2 );
        if ( $linecount <= 32 )      $tmp = array ( 10,5,1 );
        elseif ( $linecount <= 40 )  $tmp = array ( 10,4.5,1 );
        elseif ( $linecount <= 50 )  $tmp = array ( 10,4,1 );
        elseif ( $linecount <= 54 )  $tmp = array ( 10,4.5,0 );
        elseif ( $linecount <= 60 )  $tmp = array ( 10,4,0 );
        elseif ( $linecount <= 70 )  $tmp = array ( 9,3.5,0 );
        elseif ( $linecount <= 75 )  $tmp = array ( 9,3.5,0 );
        else                         $tmp = array ( 10,4,1 );
    $fontsize = $tmp [0];
    $cellheight = $tmp [1];
    $isBigTable = count ( $table ) > 13 ? 0 : $tmp [2];

    
    $pageWidth = 210;
    $pdf->AddPage ();
    $pdf->SetLeftMargin ( 10 );
    $pdf->SetRightMargin ( 7 );
    $pdf->SetAutoPageBreak(true, 15);

    // Berechnung der Breite der rechten Spalte
    $infowidth = $pdf->GetStringWidth ( "SG Oesede-Gmth. 3 - SG Oesede-Gmth. 3" );
    $columnwidth = ( $isBigTable || !$options ['showTabelle'] )
                 ? $infowidth : max ( $infowidth, $tablewidth );


    $widthOfGames = $pageWidth - 2*10 - $columnwidth - 3;
    {
        $width[0] = $options ['showPassNr']
                  ? ( $prefs['spielDreistelligeNr'] ? $pdf->GetStringWidth ( "616" ) : $pdf->GetStringWidth ( "24" ) ) + 3
                  : 0;

        $width[1] = $pdf->GetStringWidth ( "SG Oesede-Gmth. 3" ) + 3;             // Bemerkungs-Sternchen

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

    // evtl. Spaltenwechsel
    if ( !$isBigTable )
    {
        $pdf->SetLeftMargin ( $pageWidth - 10 - $columnwidth );
        $pdf->SetY ( $columnY );
    }

    // evtl. Spaltenwechsel, aber nicht wenn unter der Tabelle Platz ist
    if ( $isBigTable && $pdf->GetY () > 150 )
    {
        $pdf->SetLeftMargin ( 10 + $widthOfGames + 3 );
        $pdf->SetY ( $columnY );
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