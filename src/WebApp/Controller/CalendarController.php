<?php

namespace Nsv\WebApp\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController {

  #[Route('/v3/termine', name: 'calendar')]
  public function calendar(): Response {
    return $this->render('calendar/calendar.html.twig', []);
  }
}
