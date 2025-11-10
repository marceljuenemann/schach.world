<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Core\Encoding;
use Nsv\League\Core\Regulation;
use Nsv\League\Core\Result;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Round;
use Nsv\League\Repository\PairingRepository;

class PgnService {

  public function __construct(
    private PairingRepository $pairingRepository
  ) {}

  public function renderPgn(Division $division, Round $round): string {
    $result = "\xEF\xBB\xBF"; // UTF-8 BOM
    foreach ($this->pgnData($division, $round) as $game) {
      foreach ($game as $key => $value) {
        $result .= "[$key \"" . Encoding::utf8_encode($value) . "\"]\n";
      }
      $result .= "\n" . $game['Result'] . "\n\n";
    }
    return $result;
  }

  private function pgnData(Division $division, Round $round): array {
    $games = [];
    $pairings = $this->pairingRepository->findByRound($division, $round->round);

    $event = $division->league->name . ' - ' . $division->name;
    $date = $round->date;
    $table = 1;
 
    foreach ($pairings as $pairing) {
      $host = $pairing->host ?? $pairing->team1;
      $site = $host->venueCity ?: '?';
      $pairingDate = $pairing->customDate ?? $date;

      foreach ($pairing->games() as $game) {
        if (!$game->player1 && !$game->player2) {
          continue;
        }

        $headers = [
          'Event' => $event,
          'Site' => $site,
          'Date' => str_replace('-', '.', $pairingDate ?? '????-??-??'),
          'Round' => $round->round . '.' . $table . '.' . $game->board,
        ];

        if ($isWhite = Regulation::isWhiteGame(true, $game->board, $division->league)) {
          $headers['White'] = $game->player1->lastName . ', ' . $game->player1->firstName;
          $headers['WhiteElo'] = $game->player1->elo ?: $game->player1->dwz ?: '-';
          $headers['WhiteTeam'] = $pairing->team1->nameWithNumber();
          $headers['Black'] = $game->player2->lastName . ', ' . $game->player2->firstName;
          $headers['BlackElo'] = $game->player2->elo ?: $game->player2->dwz ?: '-';
          $headers['BlackTeam'] = $pairing->team2->nameWithNumber();
        } else {
          $headers['White'] = $game->player2->lastName . ', ' . $game->player2->firstName;
          $headers['WhiteElo'] = $game->player2->elo ?: $game->player2->dwz ?: '-';
          $headers['WhiteTeam'] = $pairing->team2->nameWithNumber();
          $headers['Black'] = $game->player1->lastName . ', ' . $game->player1->firstName;
          $headers['BlackElo'] = $game->player1->elo ?: $game->player1->dwz ?: '-';
          $headers['BlackTeam'] = $pairing->team1->nameWithNumber();
        }

        if ($game->result1 == Result::UNKNOWN || $game->result2 == Result::UNKNOWN) {
          $headers['Result'] = '*';
        } else {
          $r1 = $game->result1 == Result::DRAW() ? '1/2' : $game->result1;
          $r2 = $game->result2 == Result::DRAW() ? '1/2' : $game->result2;
          $headers['Result'] = $isWhite ? ($r1 . '-' . $r2) : ($r2 . '-' . $r1);
        }

        $games[] = $headers;
      }

      $table++;
    }
    return $games;
  }
}
