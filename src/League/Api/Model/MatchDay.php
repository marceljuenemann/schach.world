<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\Round;

/**
 * A MatchDay is a collection of all pairings of a single round.
 */
class MatchDay
{
  public int $round;
  public ?string $date;
  public string $uri;
  public string $uriPdf;
  public string $uriApi;

  public array $pairings = array();
  public ?array $ranking;
  public ?string $comment;
  public ?array $lateRegisteredPlayers;
  public ?MatchDay $nextMatchDay;
  public ?string $lastModified;

  public static function fromRound(Round $round): MatchDay {
    $result = new MatchDay();
    $result->round = $round->round;
    $result->date = $round->date;
    $result->uri = $round->uri();
    $result->uriPdf = $round->pdfUri();
    $result->uriApi = $round->apiUri();
    return $result;
  }

  public static function compare(MatchDay $a, MatchDay $b) {
    if ($a->date == $b->date) return $a->round - $b->round;
    if ($a->date === null) return 1;
    if ($b->date === null) return -1;
    return $a->date < $b->date ? -1 : 1;
  }
}
