<?php

namespace League\Api\Service;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Snapshots\MatchesSnapshots;

class StatisticsServiceSnapshotCurlTest extends TestCase {

  use MatchesSnapshots;

  public function setUp(): void {
    $this->baseUrl = $_SERVER['TEST_BASE_URL'];
  }

  #[DataProvider('divisionDataProvider')]
  public function testFullStatisticsHtml($league, $division): void
  {
    $url = $this->baseUrl . 'ligen/' . $league . '/' . $division . '/statistik';
    $html = $this->checkUrl($url);
    self::assertMatchesHtmlSnapshot($html);
  }

  public static function divisionDataProvider(): \Generator {
    yield 'Bezirk Hannover Kreisliga Ost 17/18' => ['bezirk1-1718', 'kreisliga-ost'];
    yield 'Bezirk Hannover Bezirksliga 18/19' => ['bezirk1-1819', 'bezirksliga'];
    yield 'Bezirk 3 Bezirksklasse 21/22' => ['bezirk3-2122', 'bezirksklasse'];
    yield 'Landesliga Süd 21/22' => ['nsv-2122', 'landesliga-sued'];
    yield 'Verbandsliga Nord 22/23' => ['nsv-2223', 'verbandsliga-nord'];
  }


  /**
   * Run a curl request on the url and extract HTTP status code
   * and error description from HTML title.
   */
  public function checkUrl(string $url) {
    $cSession = $this->startCurlSession();
    $response = NULL;
    if ($cSession) {
      curl_setopt($cSession, CURLOPT_URL, $url);
      $html = curl_exec($cSession);
    }
    return $html;
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

}