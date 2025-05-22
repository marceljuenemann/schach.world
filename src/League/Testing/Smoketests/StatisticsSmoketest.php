<?php

namespace Nsv\League\Testing\Smoketests;

use Nsv\Util\Testing\Smoketest\SmoketestInterface;
use Nsv\Util\Testing\Smoketest\SmoketestProvider;
use Psr\Log\LoggerInterface;

class StatisticsSmoketest extends SmoketestProvider implements SmoketestInterface {

  /**
   * @inheritDoc
   */
  public function urls(): array {
    // TODO: Implement urls() method.
  }

  /**
   * @inheritDoc
   */
  public function transport(): string {
    // TODO: Implement transport() method.
  }

  public function execute() {
    $this->logger->info('Logs auftrennen ist gut.');
    return ('I would be a smoketest');
  }
}