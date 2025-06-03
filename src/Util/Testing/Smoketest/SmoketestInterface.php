<?php

namespace  Nsv\Util\Testing\Smoketest;

/**
 * Interface for smoketest classes.
 */
interface SmoketestInterface {

  /**
   * Since we are querying our application like we would query an
   * external URL, the base URL needs to be provided.
   */
  public function getBaseURL(): string;

  /**
   * Provide an array of URLS to run HTTP requests against.
   * Provide urls with a leading slash like /ligen/(...)
   */
  public function urls(): array;

  /**
   * Choose a messenger transport.
   */
  public function transport(): string;
}