<?php

namespace Nsv\League\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use ZipArchive;

/**
 * Controller for data exports.
 */
#[Route('/ligen/{league}/', name: 'league_export_')]
class ExportController extends AbstractLeagueController {

  /**
   * SWI Export for DWZ calculation.
   */
  #[Route('{divisionPath}/swi/', name: 'division_swi')]
  public function divisionSwi(string $divisionPath): Response {
    $this->division = $this->league->divisionByPath($divisionPath);
    $this->initializeLegacySystem();
    require_once("turnier.inc.php");
    require_once('../_module/export/swi.inc.php');

    ob_start();
    $main = new \SWI_Main($this->division->id);
    $main->main();
    $body = ob_get_clean();

    $response = new Response($body);
    $response->headers->set('Content-Type', 'text/plain');
    $response->setCharset('CP850');
    return $response;
  }

  /**
   * ZIP file with SWI exports for all divisions.
   */
  #[Route('swi/', name: 'swi')]
  public function swiZip(): Response {
    $tmpfile = tempnam(sys_get_temp_dir(), "sed_export_zip");
    $zip = new ZipArchive();
    $res = $zip->open($tmpfile, ZipArchive::CREATE);
    if (!$res) throw new \Exception("Could not create zip file");

    foreach ($this->league->divisions as $division) {
      $content = $this->divisionSwi($division->path())->getContent();
      $zip->addFromString($division->path() . '.swi', $content);
    }
    $zip->close();

    $response = new BinaryFileResponse($tmpfile);
    $response->setContentDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        'swi-' . $this->league->path . '.zip'
    );
    $response->deleteFileAfterSend(true);
    
    return $response;
  }
}
