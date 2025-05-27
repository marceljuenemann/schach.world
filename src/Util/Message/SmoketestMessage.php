<?php

namespace Nsv\Util\Message;

final class SmoketestMessage {
  /*
   * Add whatever properties and methods you need
   * to hold the data for this message class.
   */

  public function __construct(private string $className) {}

  public function getClassName(): string {
    return $this->className;
  }
}
