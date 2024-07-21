<?php

namespace Nsv\Registration\Controller;

use Nsv\Registration\Api\Request\RegisterPlayerRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

// See /ng/src/registration/types.ts for schema.
const TEST_CONFIG = [
  'tournamentName' => 'Testturnier 2024',
  'groups' => [
    [
      'id' => 'A',
      'name' => 'Gruppe A (ab DWZ 1750)'
    ],
    [
      'id' => 'B',
      'name' => 'Gruppe B (DWZ 1500-1750)'
    ],
    [
      'id' => 'C',
      'name' => 'Gruppe C (bis DWZ 1500)'
    ],
    [
      'id' => 'U18',
      'name' => 'Altersklasse U18'
    ],
    [
      'id' => 'U16',
      'name' => 'Altersklasse U16'
    ],
    [
      'id' => 'U14',
      'name' => 'Altersklasse U14'
    ],
    [
      'id' => 'U12',
      'name' => 'Altersklasse U12'
    ],
    [
      'id' => 'U10',
      'name' => 'Altersklasse U10'
    ]
  ]
];

#[Route('/v3/anmeldung/', name: 'registration_')]
class RegistrationController extends AbstractController {

  function __construct(

    ) {}

  #[Route('{tournament}/', name: 'registration')]
  public function registration(): Response {
    return $this->render('@registration/registration.html.twig', [
      'reg_config' => json_encode(TEST_CONFIG)
    ]);
  }

  #[Route('api/{tournament}/players/', methods: ['POST'], name: 'players_register')]
  public function registerPlayer(#[MapRequestPayload] RegisterPlayerRequest $request): Response {
    return new JsonResponse(['status' => 'Hello!']);
  }
}
