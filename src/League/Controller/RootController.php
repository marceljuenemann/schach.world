<?php

namespace Nsv\League\Controller;

use Nsv\League\Repository\DivisionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for URLs that are not specific to any league.
 * 
 * Note that this controller does not extend AbstractLeagueController.
 */
#[Route('/ligen/', name: 'league_root_')]
class RootController extends AbstractController {

  function __construct(
    private DivisionRepository $divisionRepository
  ) {}

  #[Route('', name: 'root')]
  public function root(): Response {
    // Redirect to an overview page outside the league manager.
    return $this->redirect('/alle-ligen/');
  }

  #[Route('3/{divisionId}', name: 'division_login')]
  public function divisionLogin(int $divisionId): Response {
    $division = $this->divisionRepository->find($divisionId);
    return $this->redirect($division->league->uri() . '?m=startseite&staffel=' . $division->id);
  }
}
