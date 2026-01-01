<?php

namespace Nsv\League\Export\Pdf;

use Nsv\League\Api\Model\MatchDay;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Nsv\Util\Pdf\Cell;
use Nsv\Util\Pdf\Pdf;
use Nsv\Util\Pdf\Text;

/**
 * TODO:
 * - Main layout
 * - Comments:  ¹ ²	³ *
 * - URIs
 * - Adjust header and footer margins
 * - Render a footer
 * - Move into Service directory
 * - Move Service directory out of Api/
 * - Add tests
 *   - NSV (5 x 8)
 *   - U12 (6 x 2 x 4)
 *   - Two matches
 *   - Multi page
 *   - Edge cases (comments)
 *      - Lots of comments https://localhost:6464/ligen/pokal-1516/pokal-mm/1/
 *      - Comments with new line
 *      - Comments with HTML (ignore)
 */
class MatchDayPdf {

  private Pdf $pdf;
  private PairingList $pairingList;
  private Ranking | null $ranking = null;

  public function __construct(private Division $division, private MatchDay $matchDay) {
    $this->pdf = new Pdf();
    $this->pairingList = new PairingList($matchDay);
    if (!empty($matchDay->legacyRanking)) {
      $this->ranking = new Ranking($matchDay->legacyRanking);
    }
  }

  public function render() {
    $this->renderHeader();
    $this->pdf->render($this->pairingList);

    if ($this->ranking) {
      $this->ranking->layout($this->pdf);
      $this->ranking->render($this->pdf);
    }

    $this->pdf->Ln();
    $this->renderInfos();
  }
  
  private function renderHeader() {
    $cell = new Cell();
    $cell->text = $this->division->league->name;
    $cell->fontSize = 12;
    $cell->align = 'C';
    $this->pdf->render($cell);

    $cell = new Cell();
    $cell->text = $this->division->name;
    $cell->fontSize = 16;
    $cell->align = 'C';
    $cell->fontStyle = 'B';
    $this->pdf->render($cell);

    $text = $this->matchDay->round . '. Spieltag';
    if ($this->matchDay->date) {
      // TODO: emdash (requires unicode?)
      $text .= ' - ' . date('d.m.Y', date_create($this->matchDay->date)->getTimestamp());
    }
    $cell = new Cell();
    $cell->text = $text;
    $cell->fontSize = 12;
    $cell->align = 'C';
    $this->pdf->render($cell);

    $this->pdf->Ln();  // TODO: padding
  }

  private function renderInfos() {
    $this->pdf->x = $this->pdf->w - 70;
    $this->pdf->lMargin = $this->pdf->x; // TODO: Just a test. Reset.

    $this->renderComments();
    $this->renderLateRegistrations();
    $this->renderNextMatchDayPreview();
  }

  private function renderComments() {
    if (isset($this->matchDay->comment) && $this->matchDay->comment) {
      $this->pdf->render(new Cell('Anmerkungen', 'B'));
      $this->pdf->render(new Text($this->matchDay->comment));
      $this->pdf->Ln();
    }
    if (count($this->pairingList->remarks()) > 0) {
      foreach ($this->pairingList->remarks() as $remark) {
        $this->pdf->render($remark);
      }
      $this->pdf->Ln();
    }
  }

  private function renderLateRegistrations() {
    if (isset($this->matchDay->lateRegisteredPlayers) && $this->matchDay->lateRegisteredPlayers) {
      foreach ($this->matchDay->lateRegisteredPlayers as $teamId => $players) {
        $team = $this->division->league->teamById($teamId);
        $this->pdf->render(new Cell('Nachmeldungen ' . $team->name, 'B'));
        foreach ($players as $player) {
          $text = $player->number . ' ' . $player->name;
          if ($player->lateRegistrationRound !== $this->matchDay->round) {
            $text .= ' (ab ' . $player->lateRegistrationRound . '. Spieltag)';
          }
          $this->pdf->render(new Text($text));
        }
        $this->pdf->Ln();
      }
    }
  }

  private function renderNextMatchDayPreview() {
    if ($this->matchDay->nextMatchDay()) {
      $matchDay = $this->matchDay->nextMatchDay();
      $text = Encoding::utf8_decode('Nächster Spieltag');
      if ($matchDay->date) {
        // TODO: Add a date format method
        $text .= ' (' . date('d.m.Y', date_create($matchDay->date)->getTimestamp()) . ')';
      }
      $this->pdf->render(new Cell($text, 'B'));

      foreach ($matchDay->pairings as $pairing) {
        $text = $pairing->team1->name . ' - ' . $pairing->team2->name;
        if ($pairing->wasMoved) {
          $text .= ' (' . date('d.m.Y', date_create($pairing->moveDate)->getTimestamp()) . ')';
        }
        $this->pdf->render(new Text($text));
      }
      $this->pdf->Ln();
    }
  }

  /*

          // Schrift kleiner
        $pdf->SetFont ( "", "", 9 );

        // Bemerkungen
        $content = "<B>Bemerkungen</B><BR>";
        foreach ( $bemerkungen as $bemerkung )
            $content .= "$bemerkung[0]$bemerkung[1]<BR><BR>";
        if ( isset ( $data ['bemerkung'] ) && $data ['bemerkung'] )
            $content .= str_replace ( "\r\n", "<BR>", str_replace ( "\n", "<BR>", $data ['bemerkung'] ) )."<BR>";

        // Nachmeldungen
        if ( count ( $data ['nachmeldungen'] ) && $options ['showNachmeldungen'] )
        {
            $lastteam = "";
            foreach ( $data ['nachmeldungen'] as $nachmeldung )
                if ( $nachmeldung ['berechtigtAb'] == $_GET ['r'] || $nachmeldung ['berechtigtAb'] == $_GET ['r'] + 1 )
                {
                    // Für jede Mannschaft eine Überschrift ausgeben
                    if ( $nachmeldung ['mannschaft'] != $lastteam )
                    {
                        $lastteam = $nachmeldung ['mannschaft'];
                        $content .= "<BR><B>Nachmeldung $lastteam</B><BR>";
                    }

                    // Nun den Spieler ausgeben
                    if ( $options ['showPassNr'] )
                        $content .= "$nachmeldung[passnr] ";
                    $content .= "<A HREF='$urlbase?spieler=$nachmeldung[id]'>$nachmeldung[fullname]</A>";

                    if ($nachmeldung ['berechtigtAb'] != $_GET ['r'])
                        $content .= " (ab $nachmeldung[berechtigtAb]. Spieltag)<BR>";
                    else
                        $content .= "<BR>";
                }
        }

        // Spieltag Vorschau
        if ( $data ['vorschau'] && $options ['showSpieltagvorschau'] )
        {
            $content .= utf8_decode("<BR><B>Nächster Spieltag (".$data ['vorschautermin'].")</B><BR>");
            $xtra = "";
            foreach ( $data ['vorschau'] as $paarung )
            {
                $content .= "$xtra$paarung[mannschaft1] - $paarung[mannschaft2]";
                $content .= $paarung['verlegung'] ? " ($paarung[verlegung])" : '';
                $xtra = "<BR>";
            }
        }
*/



  public function getResponse() {
    return $this->pdf->asResponse('Hello.pdf', Encoding::UNICODE_ENABLED);
  }
}
