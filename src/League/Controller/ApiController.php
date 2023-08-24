<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Request\CreateDivisionRequest;
use Nsv\League\Api\Request\DivisionOrderRequest;
use Nsv\League\Api\Service\DivisionService;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Core\Auth;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/ligen/{league}/api/', name: 'league_api_')]
class ApiController extends AbstractLeagueController {

  function __construct(
    private Auth $auth,
    private ValidatorInterface $validator
  ) {}

  /**
   * *Unstable* API for displaying a pairing list in the admin area.
   * 
   * TODO: Remove the pairing list and instead let users enter results directly
   * via the match day or overview routes. 
   */
  #[Route('unstable/pairings/', name: 'pairings')]
  public function pairings(ScheduleService $scheduleService): Response {
    $this->checkLeagueAccess();
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

  #[Route('divisions/create/', methods: ['POST'], name: 'division_create')]
  public function createDivision(#[MapRequestPayload] CreateDivisionRequest $request, DivisionService $service): Response {
    $this->checkLeagueManagerAccess();
    $service->createDivision($this->league, $request);
    return $this->apiResponse();
  }

  #[Route('divisions/order/', methods: ['PUT'], name: 'division_order')]
  public function reorderDivisions(#[MapRequestPayload] DivisionOrderRequest $request, DivisionService $service): Response {
    $this->checkLeagueManagerAccess();
    $service->updateOrder($this->league, $request);
    return $this->apiResponse();
  }

  
  // TODO: Move somewhere more general?
  private function checkLeagueAccess() {
    $this->auth->checkManagerAccess($this->league);
  }

  private function checkLeagueManagerAccess() {
    if ($this->auth->checkManagerAccess($this->league) != null) {
      throw new AccessDeniedHttpException('League manager access required');
    }
  }

  private function checkDivisionAccess(int $divisionId) {
    $division = $this->auth->checkManagerAccess($this->league);
    if ($division && $division->id != $divisionId) {
      throw new AccessDeniedHttpException('No access to this division');
    }
  }


  /*
  TODO: enable NSV ApiErrors like this:
  
  $error = [
    'errorType' => 'nsv',
    'errorMessages' => [
      'Fehler 3000',
      'Fehler 4000'
    ]
  ];
  $response = $this->apiResponse($request);
  $response->setStatusCode(422);
  return $response;
  */
}
