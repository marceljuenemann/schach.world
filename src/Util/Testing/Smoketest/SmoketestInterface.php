<?php

namespace  Nsv\Util\Testing\Smoketest;

/**
 * Interface for smoketest classes.
 */
interface SmoketestInterface {

  /**
   * Provide an array of URLS to run HTTP requests against.
   */
  public function urls(): array;

  /**
   * Choose a messenger transport.
   */
  public function transport(): string;

  /**
   * Run the smoketest. The actual custom logic.
   */
  public function execute();
}