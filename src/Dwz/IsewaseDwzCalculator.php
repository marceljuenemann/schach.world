<?php

namespace Nsv\Dwz;

use SimpleXMLElement;

/**
 * Performs inofficial DWZ rating calculation using the isewase.de service.
 */
// TODO: Unit test
class IsewaseDwzCalculator
{
  const SERVICE_URL = "http://d2.isewase.de/dwzxml2.php";
  const TIMEOUT_SECONDS = 1;
  
  private $service;

  function __construct(callable|null $service = null) {
    $this->service = $service ?: function($params): SimpleXMLElement {
      $url = self::SERVICE_URL . '?' . http_build_query($params);        
      $ctx = stream_context_create(["http" => ["timeout" => self::TIMEOUT_SECONDS]]);
      return new SimpleXMLElement(file_get_contents($url, 0, $ctx));
    };
  }

  /**
   * @param dwz current DWZ
   * @param opponentDwz array of the DWZ of opponents. Only opponents with DWZ may be included
   * @param point the score of those games
   * @param yearOfBirth YOB of the player
   * @return array|null with the results of the calculation or null if no calculation was performed.
   */
  function calculate(int|null $dwz, array $opponentDwz, float $points, int|null $yearOfBirth): array|null {
    $n = count($opponentDwz);
    if (!$n) return null;
    if (!$dwz && $n < 5) return null;
    
    $params = [
      'ed' => $dwz,
      'gj' => $yearOfBirth ?: '',
      'pu' => $points,
      'gd' => implode(';', $opponentDwz)
    ];
    $xml = call_user_func($this->service, $params);

    $result ['Partien'] = $n;
    $result ['Alte DWZ'] = $dwz;
    $result ['Gegner DWZ'] = (int) $xml->Auswertung->Niveau;
    if ($dwz) {
      $result ['Erwartung'] = (float) $xml->Auswertung->Gewinnerwartung;
    }
    $result ['Punkte'] = $points;
    if ((int) $xml->Auswertung->Leistung) {
      $result ['Leistung'] = (int) $xml->Auswertung->Leistung;
    }
    $result ['Neue DWZ'] = (int) $xml->Auswertung->DWZneu;
    if ($dwz) {
      $result ['Differenz'] = (int) $xml->Auswertung->DWZneu - (int) $dwz;
    }
    return $result;
  }
}
