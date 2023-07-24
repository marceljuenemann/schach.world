<?php

namespace Nsv\League\Controller;

use Nsv\League\Api\Service\ScheduleService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the main publicly accessible routes.
 * 
 * TODO: Merge with Division and Team controller
 */
#[Route('/ligen/{league}/', name: 'league_')]
class MainController extends AbstractLeagueController {

  #[Route('overview/', name: 'overview')]
  public function overview(
    #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\d{4}-\d{2}-\d{2}$/'])]
    ?string $date,
    ScheduleService $service
  ): Response {
    $today = date('Y-m-d');
    $allDates = $service->leagueDates($this->league);
    // TODO: handle case if no dates found at all. Show a beatiful info message :)
    $dateToShow = $date ?: $service->closestDate($allDates, $today);
    $matches = $service->matchesByDate($this->league, $dateToShow);


    // Show at most three future dates and at most five tabs in total.
    $pos = array_search($dateToShow, $allDates);
    $tabs = array_slice($allDates, 0, $pos + 4);
    $tabs = array_slice($tabs, max(count($tabs) - 5, 0));
    
    return $this->renderWithLegacySystem('overview.html.twig', [
      'tabs' => $tabs,
      'activeTab' => $dateToShow,
      'matches' => $matches
    ]);
  }
}
