<?php

namespace Nsv\Util\Testing\Smoketest;

use Psr\Log\LoggerInterface;

/**
 * Runs smoketests against a given array of URLs.
 */
class SmoketestProvider {

  public function __construct(protected LoggerInterface $logger) {}

  public function getBaseURL(): string {
    return 'https://nsv-online.local/';
  }

  public function urls(): array {
  }

  public function transport(): string {
    // TODO: Implement transport() method.
  }

  public function execute() {}



  protected function checkUrls(): array {
    $baseUrl = $this->getBaseURL();
    $urls = $this->urls();
    $cSession = $this->startCurlSession();
    $responses = [];
    foreach ($urls as $url) {
      $responses[] = $this->requestUrl($baseUrl . $url, $cSession);
    }
    return $responses;
  }

  protected function requestUrl(string $url, $cSession = null) {
    curl_setopt($cSession, CURLOPT_URL, $url);
    $html = curl_exec($cSession);

    $info = curl_getinfo($cSession);
    return $info;
  }

  private function get_html_title($html) {
    preg_match("/\<title.*\>(.*)\<\/title\>/isU", $html, $matches);
    return $matches[1];
  }

  private function startCurlSession(){
    if($this->isCurlInstalled()) {
      $cSession = curl_init();
      curl_setopt($cSession, CURLOPT_RETURNTRANSFER, TRUE);
      curl_setopt($cSession, CURLOPT_HEADER, FALSE);
      curl_setopt($cSession, CURLOPT_SSL_VERIFYPEER, FALSE);
      return $cSession;
    }
  }

  private function isCurlInstalled() {
    if  (in_array  ('curl', get_loaded_extensions())) {
      return true;
    }
    else {
      return false;
    }
  }
}