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
    $exactMatch = !!$date;
    $today = '2023-03-05'; //date('Y-m-d');
    $dateToShow = $date ?: $today;
    $overview = $service->leagueOverview($this->league, $dateToShow, $exactMatch);

    // TODO: move to API just for easier testing?
    // TODO: handle case if no dates found at all.

    // Generate tabs: All dates + today.
    $tabs = $overview->allDates;
    if (!in_array($today, $tabs)) {
      // Insert a 'current' tab, identified as null.
      foreach ($tabs as $index => $tab) {
        if ($tab > $today) {
          // Insert here.
          array_splice($tabs, $index, 0, [null]);
          break;
        }
      }
      // Add to end of array if not inserted yet.
      if (count($tabs) == count($overview->allDates)) {
        $tabs[] = null;
      }
    }

    if (count($overview->datesShown) == 1) {
      $activeTab = reset($overview->datesShown);
    } else {
      $activeTab = null;
    }
    
    return $this->renderWithLegacySystem('overview.html.twig', [
      'divisions' => $overview->divisions,
      'tabs' => $tabs,
      'activeTab' => $activeTab
    ]);
  }

  #[Route('overview/unstable-api/', name: 'overview_api')]
  public function overview_api(
    #[MapQueryParameter(filter: \FILTER_VALIDATE_REGEXP, options: ['regexp' => '/^\d{4}-\d{2}-\d{2}$/'])]
    ?string $date,
    ScheduleService $service
  ): Response {
    $exactMatch = !!$date;
    $today = '2023-01-05'; //date('Y-m-d');
    $date = $date ?: $today;
    $overview = $service->leagueOverview($this->league, $date, $exactMatch);

    return $this->apiResponse($overview);
  }
}
