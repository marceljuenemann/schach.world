<?php

namespace Nsv\WebApp\Controller;

use Nsv\WebApp\Entity\Event;
use Nsv\WebApp\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;

class CalendarController extends AbstractController {

  #[Route('/v3/termine/', name: 'calendar')]
  public function calendar(EventRepository $eventRepository): Response {
    $events = $eventRepository->getUpcoming();
    return $this->render('calendar/calendar.html.twig', ['events' => $events]);
  }

  #[Route('/v3/termine/eintragen/', name: 'calendar-add')]
  public function addEntry(Request $request): Response {
    $event = new Event();
    $form = $this->createFormBuilder($event)
        ->add('date', DateType::class, [
          'label' => 'Datum',
          'widget' => 'single_text',
          'format' => 'yyyy-MM-dd',
          'constraints' => [new GreaterThanOrEqual(date('Y-m-d'))]
        ])
        ->add('name', TextType::class)
        ->add('url', UrlType::class, ['label' => 'Link'])
        ->add('captcha', IntegerType::class, [
          'label' => 'Wie viele Felder hat ein Schachbrett?',
          'mapped' => false,
          'constraints' => [new EqualTo(64)],
        ])
        ->add('eintragen', SubmitType::class)
        ->getForm();
 
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        $event = $form->getData();
        
        print_r($event);
        // TODO: insert row
        // TODO: send eMail
        // TODO: Admin verification
        // TODO: redirect and show message

        //return $this->redirectToRoute('task_success');
    }

    return $this->render('calendar/add.html.twig', [
      'form' => $form
    ]);
  }
}
