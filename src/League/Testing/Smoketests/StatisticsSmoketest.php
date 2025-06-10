<?php

namespace Nsv\League\Testing\Smoketests;

use Nsv\Util\Testing\Smoketest\SmoketestInterface;
use Nsv\Util\Testing\Smoketest\SmoketestProvider;
use Psr\Log\LoggerInterface;

class StatisticsSmoketest implements SmoketestInterface {

  /**
   * @inheritDoc
   */
  public function baseUrl(): string {
    return '';
  }

  /**
   * @inheritDoc
   */
  public function routes(): array {
//    $url_1 = '/ligen/test-2022/beirksliga/3';
//    $url_2 = '/ligen/test-2022/bezirksliga/4';
    $url_1 ='https://httpstat.us/500';
    $url_2 ='https://httpstat.us/200';
    return [$url_1, $url_2];
  }

  /**
   * @inheritDoc
   */
  public function transport(): string {
    // TODO: Implement transport() method.
  }
}