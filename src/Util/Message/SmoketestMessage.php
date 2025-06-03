<?php

namespace Nsv\Util\Message;

final class SmoketestMessage {
  /*
   * Add whatever properties and methods you need
   * to hold the data for this message class.
   */

  public function __construct(private string $url) {}

  public function getUrl(): string {
    return $this->url;
  }
}
