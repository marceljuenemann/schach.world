<?php

namespace Nsv\Dwz\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the DWZ API.
 */
#[Route('/dwz/api/', name: 'dwz_')]
class DwzController extends AbstractController {

  #[Route('players/', name: 'players')]
  public function players(): Response {
    return new Response("Hello World!");
  }
}
