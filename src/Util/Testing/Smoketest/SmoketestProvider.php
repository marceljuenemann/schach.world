<?php

namespace Nsv\Util\Testing\Smoketest;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Runs smoketests against a given array of URLs.
 */
class SmoketestProvider implements SmoketestInterface {


  public function __construct(protected LoggerInterface $logger) {}

  public function urls(): array {
  }

  public function transport(): string {
    // TODO: Implement transport() method.
  }

  public function execute() {}

  protected function checkUrls(): array {
    $urls = $this->urls();
    $responses = [];
    foreach ($urls as $url) {
      $responses[] = $this->requestUrl($url);
    }
    return $responses;
  }

  protected function requestUrl(string $url): Response {
    $request = Request::create($url);
    $response = new Response();
    $response->prepare($request);
    return $response;
  }
}