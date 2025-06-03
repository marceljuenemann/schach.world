<?php

namespace Nsv\Util\MessageHandler;

use Nsv\Util\Message\SmoketestMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SmoketestMessageHandler {

  public function __invoke(SmoketestMessage $message): void {
    $url = $message->getUrl();

    $responses = $this->checkUrls();
    if(!is_null($responses)) {
      foreach ($responses as $response) {
        $this->logger->info($response);
      }
    }

    $willi = 'Schuhe';
  }

  private function checkUrls(): array {
    $baseUrl = $this->getBaseURL();
    $urls = $this->urls();
    $cSession = $this->startCurlSession();
    $responses = null;
    if($cSession) {
      $responses = [];
      foreach ($urls as $url) {
        $responses[] = $this->requestUrl($baseUrl . $url, $cSession);
      }
    }
    // Make sure in case of missing curl something is written
    // to the log or it can handle the empty array in responses
    // and does not create an error.
    return $responses;
  }

  private function requestUrl(string $url, $cSession = null) {
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
      curl_setopt($cSession, CURLOPT_FOLLOWLOCATION, TRUE);
      return $cSession;
    } else {
      return FALSE;
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
