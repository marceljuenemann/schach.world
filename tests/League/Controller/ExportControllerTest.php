<?php

namespace Tests\League\Controller;

use Tests\League\LeagueWebTestCase;
use ZipArchive;

class ExportControllerTest extends LeagueWebTestCase
{
  public function testDwzExport_requiresLogin() {
    $this->request('GET', '/ligen/nsv-2526/landesliga-nord/unstable/dwz-export/');
    $this->assertResponseStatusCodeSame(403);
  }

  public function testDwzExport() {
    $this->login();
    $response = $this->request('GET', '/ligen/nsv-2526/landesliga-nord/unstable/dwz-export/');

    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Type', 'application/xml');
    $xml = simplexml_load_string($response->getContent());
    $this->assertSame('Landes- und Verbandsligen 2025/26', (string) $xml->tournament->label);
  }

  public function testDivisionSwi() {
    $response = $this->request('GET', '/ligen/nsv-2526/landesliga-nord/swi/');

    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Type', 'text/plain; charset=CP850');
    $this->assertStringContainsString('Fortuna Logabirum', $response->getContent());
  }

  public function testSwiZip() {
    $this->request('GET', '/ligen/nsv-2526/swi/');

    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Disposition', 'attachment; filename=swi-nsv-2526.zip');

    // The temporary file is deleted after sending, so use the captured content.
    $file = tempnam(sys_get_temp_dir(), 'swi-test');
    file_put_contents($file, $this->client->getInternalResponse()->getContent());
    $zip = new ZipArchive();
    $this->assertTrue($zip->open($file));
    $this->assertSame(6, $zip->numFiles);
    $this->assertNotFalse($zip->locateName('landesliga-nord.swi'));
    $this->assertNotFalse($zip->locateName('verbandsliga-sued.swi'));
    $zip->close();
    unlink($file);
  }
}
