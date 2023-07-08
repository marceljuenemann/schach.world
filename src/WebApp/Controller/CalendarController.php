<?php

namespace Nsv\WebApp\Controller;

use Nsv\WebApp\Entity\Event;
use Nsv\WebApp\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarController extends AbstractController {

  #[Route('/v3/termine/', name: 'calendar')]
  public function calendar(EventRepository $eventRepository): Response {
    $events = $eventRepository->getUpcoming();
    return $this->render('calendar/calendar.html.twig', ['events' => $events]);
  }

  #[Route('/v3/termine/eintragen/', name: 'calendar')]
  public function addEntry(EventRepository $eventRepository): Response {
    // creates a task object and initializes some data for this example
    $event = new Event();

    $form = $this->createFormBuilder($event)
        ->add('name', TextType::class)
        ->add('date', DateType::class)
        ->getForm();
 



    return $this->render('calendar/add.html.twig', [
      'form' => $form
    ]);
  }
}
