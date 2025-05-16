<?php

namespace  Nsv\Util\Testing\Smoketest;

/**
 * Interface for smoketest classes.
 */
interface SmoketestInferface {

  /**
   * Provide an array of URLS to run HTTP requests against.
   */
  public function urls(): array;

  /**
   * Choose a messenger transport.
   */
  public function transport(): string;
}