<?php

namespace Nsv\WebApp\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\WebApp\Core\WordPress\Auth;
use Nsv\WebApp\Entity\Event;
use Nsv\WebApp\Repository\EventRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Url;

class CalendarController extends AbstractController {

  function __construct(
    private EventRepository $eventRepository,
    private EntityManagerInterface $em,
    private MailerInterface $mailer
  ) {}

  #[Route('/v3/termine/', name: 'calendar')]
  public function calendar(): Response {
    $events = $this->eventRepository->getUpcoming();
    return $this->render('calendar/calendar.html.twig', ['events' => $events]);
  }

  #[Route('/v3/termine/eintragen/', name: 'calendar-add')]
  public function addEntry(Request $request): Response {
    $isAuthor = Auth::isAuthor();
    $event = new Event();
    $builder = $this->createFormBuilder($event)
        ->add('date', DateType::class, [
          'label' => 'Datum',
          'widget' => 'single_text',
          'format' => 'yyyy-MM-dd',
          'constraints' => [new GreaterThanOrEqual(date('Y-m-d'))]
        ])
        ->add('name', TextType::class)
        ->add('url', UrlType::class, [
          'label' => 'Link',
          'constraints' => [new Url()],
        ]);
    if ($isAuthor) {
      $builder = $builder->add('isNsv', CheckboxType::class, [
        'label' => 'Offizieller NSV Termin',
        'required' => false
      ]);
    } else {
      $builder = $builder->add('captcha', IntegerType::class, [
        'label' => 'Wie viele Felder hat ein Schachbrett?',
        'mapped' => false,
        'constraints' => [new EqualTo(64)],
      ]);
    }
    $form = $builder->add('eintragen', SubmitType::class)->getForm();
 
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      // If the user has Author permissions, approve right away. 
      $event = $form->getData();
      $event->isApproved = $isAuthor;
      
      // Store.
      $this->em->persist($event);
      $this->em->flush();

      // Approval mail and messages.
      if ($isAuthor) {
        $this->addFlash('success', 'Termin erfolgreich eingetragen');
      } else {
        $this->sendApprovalMail($event);
        $this->addFlash('info', 'Der Termin wird nach Freischaltung veröffentlicht');
      }
      return $this->redirectToRoute('calendar');
    }

    return $this->render('calendar/add.html.twig', [
      'form' => $form
    ]);
  }

  #[Route('/v3/termine/approve/{id}/', name: 'calendar-approve')]
  public function approveEntry(Request $request, int $id): Response {
    $event = $this->eventRepository->findOneById($id);
    if (!$event) throw new NotFoundHttpException();
    if (!Auth::isLoggedIn()) {
      return Auth::loginRedirect($request->getUri());
    }
    if (Auth::isAuthor()) {
      $event->isApproved = true;
      $this->em->persist($event);
      $this->em->flush();
      $this->addFlash('success', 'Termin wurde freigeschaltet');
    } else {
      $this->addFlash('danger', 'Nicht berechtigt zum Freischalten');
    }
    return $this->redirectToRoute('calendar');
  }
  
  private function sendApprovalMail(Event $event) {
    $email = (new TemplatedEmail())
      ->to($_ENV['CALENDAR_APPROVER'])
      ->subject('[NSV Terminkalender] ' . $event->name)
      ->htmlTemplate('email/calendar-approval.html.twig')
      ->context([
        'event' => $event,
        'approvalUrl' => $this->generateUrl('calendar-approve', ['id' => $event->id], UrlGeneratorInterface::ABSOLUTE_URL)
      ]);
    $this->mailer->send($email);
  }
}
