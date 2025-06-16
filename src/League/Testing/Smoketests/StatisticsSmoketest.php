<?php

namespace Nsv\League\Testing\Smoketests;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Repository\DivisionRepository;
use Nsv\Util\Testing\Smoketest\SmoketestInterface;
use Psr\Log\LoggerInterface;

class StatisticsSmoketest implements SmoketestInterface {

  public function __construct(private DivisionRepository $divisionRepository) {
  }
  /**
   * @inheritDoc
   */
  public function baseUrl(): string {
    return 'https://nsv-online.local';
  }

  /**
   * @inheritDoc
   */
  public function routes(): array {
    $routes = [];
    $allDivisions = $this->divisionRepository->findAllThatHaveLeague();
    foreach($allDivisions as $division) {
      $divisionPath = $division->uri();
      $routes[] = $divisionPath . 'statistik';
    }
    return $routes;
  }
}