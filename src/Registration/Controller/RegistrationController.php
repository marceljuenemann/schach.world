<?php

namespace Nsv\Registration\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Registration\Api\Model\PlayerRegistration;
use Nsv\Registration\Api\Request\RegisterPlayerRequest;
use Nsv\Registration\Entity as Entity;
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
      'name' => 'Gruppe B (DWZ 1500-1750)',
      'maxDwz' => 1750
    ],
    [
      'id' => 'C',
      'name' => 'Gruppe C (bis DWZ 1500)',
      'maxDwz' => 1500
    ],
    [
      'id' => 'U18',
      'name' => 'Altersklasse U18',
      'minYearOfBirth' => 2007
    ],
    [
      'id' => 'U16',
      'name' => 'Altersklasse U16',
      'minYearOfBirth' => 2009
    ],
    [
      'id' => 'U14',
      'name' => 'Altersklasse U14',
      'minYearOfBirth' => 2011
    ],
    [
      'id' => 'U12',
      'name' => 'Altersklasse U12',
      'minYearOfBirth' => 2013
    ],
    [
      'id' => 'U10',
      'name' => 'Altersklasse U10',
      'minYearOfBirth' => 2015
    ]
  ]
];

#[Route('/v3/anmeldung/', name: 'registration_')]
class RegistrationController extends AbstractController {

  function __construct(
    private EntityManagerInterface $mainEntityManager
  ) {}

  #[Route('{tournament}/', name: 'registration')]
  public function registration(string $tournament): Response {
    return $this->render('@registration/registration.html.twig', [
      'reg_config' => json_encode(TEST_CONFIG),
      'reg_players' => json_encode($this->getPlayers($tournament))
    ]);
  }

  #[Route('api/{tournament}/players/', name: 'players')]
  public function players(string $tournament): Response {
    return new JsonResponse($this->getPlayers($tournament));
  }

  #[Route('api/{tournament}/players/', methods: ['POST'], name: 'players_register')]
  public function registerPlayer(#[MapRequestPayload] RegisterPlayerRequest $request): Response {
    return new JsonResponse();
  }

  private function getPlayers(string $tournament): array {
    $repo = $this->mainEntityManager->getRepository(Entity\PlayerRegistration::class);
    $players = $repo->findByTournament($tournament);
    return array_map(fn($p) => PlayerRegistration::fromEntity($p), $players);
  }
}
