<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Model\Division;
use Nsv\League\Api\Service\ScheduleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}/{division}/', name: 'league_')]
class DivisionController extends AbstractLeagueController {

  #[Route('spielplan/', name: 'schedule')]
  public function schedule(ScheduleService $service): Response {
    $matchDays = $service->matchDays($this->division);
    return $this->renderWithLegacySystem('schedule.html.twig', ['matchDays' => $matchDays]);
  }

  #[Route('spielplan/unstable-api/', name: 'schedule_api')]
  public function schedule_api(ScheduleService $service): Response {
    $matchDays = $service->matchDays($this->division);
    return new Response(
      print_r($matchDays, true),
      200, ['Content-type' => 'text/plain; charset=latin1']);
  }
}
