<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Core\Auth;
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
  public function pairings(Auth $auth, ScheduleService $scheduleService): Response {
    $auth->checkManagerAccess($this->league);
    $today = date('Y-m-d');
    $divisions = [];
    foreach ($this->league->divisions as $division) {
      $model = Division::fromEntity($division);
      $model->matchDays = $scheduleService->matchDays($division);
      $model->closestDate = $scheduleService->closestDate(array_map(function ($date) {
        return $date->date;  // TODO: make this cleaner
      }, $division->dates()), $today);
      $divisions[] = $model;
    }
    return $this->apiResponse($divisions);
  }
}
 