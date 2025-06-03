<?php

namespace Nsv\Util\Testing\Smoketest;

use Nsv\Util\Message\SmoketestMessage;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Runs smoketests against a given array of URLs.
 */
class SmoketestProvider {

  public function getBaseURL(): string {
    return 'https://nsv-online.local';
  }

  public function urls(): array {
  }

  public function transport(): string {
    // TODO: Implement transport() method.
  }

  public function returnCompleteUrls() {
    $urls = $this->urls();
    foreach($urls as &$url) {
      $url = $this->getBaseURL() . $url;
    }
    return $urls;
  }


}