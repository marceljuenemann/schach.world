<?php

namespace Nsv\League\Core;

/**
 * Utilities for results and scores.
 */
class Result
{
  const WIN = '1';
  // TODO: Introduce DRAW once we are on Unicode everywhere. Use DRAW() until then.
  const UNICODE_DRAW = '½';
  const LOSS = '0';
  const BYE_WIN = '+';
  const BYE_LOSS = '-';
  const UNKNOWN = '?';

  /**
   * The result string for draw.
   */
  public static function DRAW(): string {
    return Encoding::utf8_decode(self::UNICODE_DRAW);
  }

  /**
   * Returns whether the game was actually a played chess game, as opposed to a bye.
   */
  public static function wasPlayed(string|null $result): bool {
    return $result !== null && in_array($result, [self::WIN, self::DRAW(), self::LOSS]);
  }

  /**
   * Returns the points scored for a given result.
   */
  public static function score(string $result): float {
    switch ($result) {
      case self::WIN:
      case self::BYE_WIN:
        return 1.0;
      
      case self::DRAW():
        return 0.5;

      default:
        return 0.0;
    }
  }

  public static function format(float $result) {
    $result = $result == 0.5 ? self::DRAW() : "$result";
    return str_replace(".5", self::DRAW(), $result);
  }
}
