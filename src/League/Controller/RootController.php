<?php

namespace Nsv\League\Controller;

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

  #[Route('', name: 'root')]
  public function root(): Response {
    // Redirect to an overview page outside the league manager.
    return $this->redirect('/alle-ligen/');
  }
}
