<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Model\Pairing;
use Nsv\League\Api\Service\ScheduleService;
use Nsv\League\Repository\PairingRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/ligen/{league}/api/', name: 'league_api_')]
class ApiController extends AbstractLeagueController {

  #[Route('pairings/', name: 'pairings')]
  public function pairings(PairingRepository $pairingRepository): Response {
    // TODO: group by division instead.
    $pairings = $pairingRepository->findByLeague($this->league->id);
    $pairings = array_map([Pairing::class, 'fromEntity'], $pairings);
    return $this->apiResponse($pairings);
  }
}
