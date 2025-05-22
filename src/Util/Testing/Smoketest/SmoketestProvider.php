<?php

namespace  Nsv\Util\Testing\Smoketest;

use Psr\Log\LoggerInterface;

/**
 * Runs smoketests against a given array of URLs.
 */
class SmoketestProvider implements SmoketestInterface {

  public function __construct(protected LoggerInterface $logger) {}

  public function urls(): array {
    // TODO: Implement urls() method.
  }

  public function transport(): string {
    // TODO: Implement transport() method.
  }

  public function execute() {
  }
}