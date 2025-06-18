<?php

namespace Nsv\Util\MessageHandler;

use Nsv\Util\Message\SmoketestMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

#[AsMessageHandler]
class SmoketestMessageHandler {

  public function __construct(private LoggerInterface $logger, private EncoderInterface $encoder) {}

  public function __invoke(SmoketestMessage $message): void {
    $url = $message->getUrl();

    $response = $this->checkUrl($url);
    if(!is_null($response)) {
      if($response['status_code'] != 200) {
        $this->logger->error($response['title'],[$this->encoder->encode($response, 'json')]);
      } else {
        $this->logger->info('HTTP Status: OK', [$this->encoder->encode($response, 'json')]);
      }
    }
  }


  /**
   * Run a curl request on the url and extract HTTP status code
   * and error description from HTML title.
   */
  public function checkUrl(string $url): array {
    $cSession = $this->startCurlSession();
    $response = NULL;
    if ($cSession) {
      curl_setopt($cSession, CURLOPT_URL, $url);
      $html = curl_exec($cSession);
      $info = curl_getinfo($cSession);

      $response = [
        'title' => $this->get_html_title($html),
        'status_code' => intval($info['http_code']),
        'url' => $url,
      ];
    }
    return $response;
  }

  /**
   * A regex to extract the HTML Title from the HTML.
   * In Symfony error messages, this contains an error description.
   */
  private function get_html_title($html): string {
    preg_match("/\<title.*\>(.*)\<\/title\>/isU", $html, $matches);
    if($matches) {
      return $matches[1];
    } else {
      return 'No title found';
    }

  }

  private function startCurlSession() {
    // Check if CURL is installed, else return FALSE.
    if (in_array('curl', get_loaded_extensions())) {
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

  private function isCurlInstalled(): bool {
    if (in_array('curl', get_loaded_extensions())) {
      return TRUE;
    } else {
      return FALSE;
    }
  }

}
