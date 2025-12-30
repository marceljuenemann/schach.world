<?php
/* Paarungstafel Bibliothek
 * 
 * Berechnet die Paarungstafel zu einer gegebenen Anzahl von
 * Mannschaften.
 * 
 * @copyright Copyright (c) 2006-2010, Marcel Jünemann
 * @version 0.8.0 (2010/7)
 * @license GNU Public License v3
 * @author Marcel Jünemann <mail@marcel-juenemann.de>
 * 
 * @package schach-ergebnisdienst
 * @subpackage libs
 */

  // Rückgabeformat:
  // runde~m$1~m$2~;runde~m$1~m$2;...;runde~m$1~m$2;
  // z.B.: 1~m4~m1~;1~m3~m2~;2~m2~m4~;...
  function Paarungstafel ( $n )
  {
    // $n == Anzahl der Mannschaften
    // $m == Paarungsnr.
    // $x == Rundennr.
    $result = "";

    // Spielfrei
    $spielfrei = $n % 2;
    $n += $spielfrei;

    // Für jede Runde
    for ( $x = 1; $x < $n; ++$x )
      // Für jede Paarung
      for ( $m = $spielfrei + 1; $m <= $n / 2; ++$m )
      {
        // Ungerade Rundenzahl?
        if ( $x % 2 == 1 )
        {
          $result .= "$x~m";
          $result .= ( ( $x - 1 ) / 2 + $m ) . "~m";
          $result .= ( $m > 1 ? ( ( $x - 1 ) / 2 - $m + $n ) % ( $n - 1 ) + 1 : $n ) . "~;";
        }

        // Gerade Rundenzahl?
        else
        {
          $result .= "$x~m";
          $result .= ( $m > 1 ? ( ( ( $n + $x ) / 2 + $m - 1 ) - 1 ) % ( $n - 1 ) + 1 : $n ) . "~m";
          $result .= ( ( $n + $x ) / 2 - $m + 1 ) . "~;";
        }
      }

    // Rückgabe
    return $result;
  }
?>
