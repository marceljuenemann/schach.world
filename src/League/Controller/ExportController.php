<?php

namespace Nsv\League\Controller;

use Nsv\League\Entity\Division;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for data exports.
 */
#[Route('/ligen/{league}/', name: 'league_export_')]
class ExportController extends AbstractLeagueController {

  /**
   * SWI Export for DWZ calculation.
   */
  #[Route('{divisionPath}/swi/', name: 'swi')]
  public function swi(string $divisionPath): Response {
    $this->division = $this->league->divisionByPath($divisionPath);
    $this->initializeLegacySystem();
    $_GET['staffel'] = $this->division->id;

    ob_start();
    require_once("turnier.inc.php");
    require('../_module/export/swi.inc.php');
    $body = ob_get_clean();
    $response = new Response($body);
    $response->setCharset('CP850');
    return $response;
  }
}
