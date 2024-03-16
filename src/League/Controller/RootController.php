<?php

namespace Nsv\League\Controller;

use Nsv\League\Repository\DivisionRepository;
use Nsv\League\Repository\TeamRepository;
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
    private DivisionRepository $divisionRepository,
    private TeamRepository $teamRepository
  ) {}

  #[Route('', name: 'root')]
  public function root(): Response {
    // Redirect to an overview page outside the league manager.
    return $this->redirect('/alle-ligen/');
  }

  #[Route('2/{param}', name: 'team_edit')]
  public function teamEdit(string $param): Response {
    $auth = substr($param, 4);
    $teamId = base_convert(substr($param, 0, 4), 36, 10);
    $team = $this->teamRepository->find($teamId);
    return $this->redirect($team->league->uri() . '?m=mannschaftsdaten&mid=' . $team->id . "&auth=$auth");
  }

  #[Route('3/{divisionId}', name: 'division_login')]
  public function divisionLogin(int $divisionId): Response {
    $division = $this->divisionRepository->find($divisionId);
    return $this->redirect($division->league->uri() . '?m=startseite&staffel=' . $division->id);
  }
}
