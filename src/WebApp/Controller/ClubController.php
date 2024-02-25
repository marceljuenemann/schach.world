<?php

namespace Nsv\WebApp\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vereine/', name: 'club_')]
class ClubController extends AbstractController {

  function __construct(
  ) {}

  #[Route('', name: 'index')]
  public function clubs(): Response {
    return $this->render('club/clubs.html.twig', ['events' => []]);
  }
}
