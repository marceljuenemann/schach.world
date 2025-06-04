<?php

namespace Nsv\Util\MessageHandler;

use Nsv\Util\Message\SmoketestMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SmoketestMessageHandler {

  public function __construct(private LoggerInterface $logger) {}

  public function __invoke(SmoketestMessage $message): void {
    $url = $message->getUrl();

    $response = $this->checkUrl($url);


    // @TODO: Add logic to only add logger message when
    // HTTP response code is not 200
    if(!is_null($response)) {
      $this->logger->info($response);
    }


    $willi = 'Schuhe';
  }

  private function checkUrl($url): array {
    $cSession = $this->startCurlSession();
    $response = NULL;
    if ($cSession) {
        $response = $this->requestUrl($url, $cSession);
    }
    // Make sure in case of missing curl something is written
    // to the log or it can handle the empty array in responses
    // and does not create an error.
    return $response;
  }

  private function requestUrl(string $url, $cSession = NULL) {
    curl_setopt($cSession, CURLOPT_URL, $url);
    $html = curl_exec($cSession);
    $info = curl_getinfo($cSession);
    $response = [
      'title' => $this->get_html_title($html),
      'status_code' => $info['http_code'],
      'url' => $url,
    ];
    return $response;
  }

  private function get_html_title($html) {
    preg_match("/\<title.*\>(.*)\<\/title\>/isU", $html, $matches);
    return $matches[1];
  }

  private function startCurlSession() {
    if ($this->isCurlInstalled()) {
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
    if (in_array('curl', get_loaded_extensions())) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
}
