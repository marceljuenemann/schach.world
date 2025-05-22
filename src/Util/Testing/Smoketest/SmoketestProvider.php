<?php

namespace Nsv\Util\Testing\Smoketest;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Runs smoketests against a given array of URLs.
 */
class SmoketestProvider implements SmoketestInterface {


  public function __construct(private HttpClientInterface $client, protected LoggerInterface $logger) {}

  public function urls(): array {
  }

  public function transport(): string {
    // TODO: Implement transport() method.
  }

  public function execute() {}

  private function getBaseURL() {
    $server = $_SERVER;
    $baseUrl = $server['REQUEST_SCHEME'] . '://' . $server['SERVER_NAME'];
    return $baseUrl;
  }

  protected function checkUrls(): array {
    $baseUrl = $this->getBaseURL();
    $urls = $this->urls();
    $responses = [];
    foreach ($urls as $url) {
      $responses[] = $this->requestUrl($baseUrl . $url);
    }
    return $responses;
  }

  protected function requestUrl(string $url) {
    $response = $this->client->withOptions(["verify_peer"=>false,"verify_host"=>false])->request('GET', $url);
    $statusCode = $response->getStatusCode();
//    $content = $response->getContent();
    //$complete = $response->toArray($content);
    return $response;
  }
}