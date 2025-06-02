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
    $url_1 = '/ligen/test-2022/beirksliga/3';
    $url_2 = '/ligen/test-2022/bezirksliga/4';
    return [$url_1, $url_2];
  }

  /**
   * @inheritDoc
   */
  public function transport(): string {
    // TODO: Implement transport() method.
  }

  public function execute() {
    //$response = $this->checkUrls($this->urls());
//    $this->logger->info('Logs auftrennen ist gut.');
    return 'I am the voice';
  }
}