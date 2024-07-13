<?php

namespace Nsv\Registration\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the DWZ API.
 */
#[Route('/anmeldung/{tournament}/', name: 'registration_')]
class RegistrationController extends AbstractController {

  function __construct(

    ) {}

  #[Route('/', name: 'registration')]
  public function registration(): Response {
    return $this->render('@registration/registration.html.twig');
  }
}
