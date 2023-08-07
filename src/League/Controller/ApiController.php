<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Model\Pairing;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Repository\PairingRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}/api/', name: 'league_api_')]
class ApiController extends AbstractLeagueController {

  /**
   * *Unstable* API for displaying a pairing list in the admin area.
   * 
   * TODO: Remove the pairing list and instead let users enter results directly
   * via the match day or overview routes. 
   */
  #[Route('unstable/pairings/', name: 'pairings')]
  public function pairings(ScheduleService $scheduleService): Response {
    $divisions = [];
    foreach ($this->league->divisions as $division) {
      $model = Division::fromEntity($division);
      $model->matchDays = $scheduleService->matchDays($division);
      $divisions[] = $model;
    }
    return $this->apiResponse($divisions);
  }
}
 