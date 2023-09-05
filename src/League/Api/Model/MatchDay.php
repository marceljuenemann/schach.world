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
  public array|null $ranking = null;
  public string|null $comment = null;
  public ?array $lateRegisteredPlayers;

  // TODO: Only allow creation from round object, can move some functions there.
  public static function create(Division $division, int $round, string|null $date = null): MatchDay {
    $result = new MatchDay();
    $result->round = $round;
    $result->date = $date;
    $result->uri = $division->matchDayUri($round);
    $result->uriPdf = $division->matchDayPdfUri($round);
    $result->uriApi = $division->matchDayApiUri($round);
    return $result;
  }

  public static function fromRound(Round $round): MatchDay {
    return self::create($round->division, $round->round, $round->date);
  }

  public static function compare(MatchDay $a, MatchDay $b) {
    if ($a->date == $b->date) return $a->round - $b->round;
    if ($a->date === null) return 1;
    if ($b->date === null) return -1;
    return $a->date < $b->date ? -1 : 1;
  }
}
