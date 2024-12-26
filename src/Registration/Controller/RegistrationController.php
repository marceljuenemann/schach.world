<?php

namespace Nsv\Registration\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Repository\PlayerRepository;
use Nsv\Registration\Api\Model\PlayerRegistration;
use Nsv\Registration\Entity as Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    private EntityManagerInterface $mainEntityManager,
    private PlayerRepository $dwzRepository
  ) {}

  #[Route('{tournament}/', name: 'registration')]
  public function registration(string $tournament): Response {
    return $this->render('@registration/registration.html.twig', [
      'title' => TEST_CONFIG['tournamentName'],
      'reg_config' => json_encode(TEST_CONFIG),
      'reg_players' => json_encode($this->getPlayers($tournament))
    ]);
  }

  #[Route('api/{tournament}/players/', methods: ['GET'], name: 'players')]
  public function players(string $tournament): Response {
    return new JsonResponse($this->getPlayers($tournament));
  }

  #[Route('api/{tournament}/players/', methods: ['POST'], name: 'players_register')]
  public function registerPlayer(string $tournament, #[MapRequestPayload] PlayerRegistration $request): Response {
    $player = new Entity\PlayerRegistration();
    $player->tournament = $tournament;
    $player->group = $request->group;
    $player->name = $request->playerData->name;
    $player->gender = $request->playerData->gender;
    $player->yearOfBirth = $request->playerData->yearOfBirth;
    $player->fideTitle = $request->playerData->fideTitle;
    $player->fideId = $request->playerData->fideId;
    $player->contactName = $request->contactDetails->name;
    $player->contactEMail = $request->contactDetails->email;

    // Find in DWZ database.
    if ($request->playerData->zps || $request->playerData->memberId) {
      $player->dwzPlayer = $this->dwzRepository->findOneBy([
        'zps' => $request->playerData->zps,
        'memberId' => $request->playerData->memberId
      ]);
      if (!$player->dwzPlayer) {
        throw new NotFoundHttpException("ZPS und Mitgliedsnummer nicht gefunden");
      }
    }

    // Store nulls to always load DWZ from reference database.
    $player->club = $request->playerData->club;
    $player->dwz = $request->playerData->dwz;
    $player->elo = $request->playerData->elo;
    if ($player->club == $player->dwzPlayer->club->name) {
      $player->club = null;
    }
    if ($player->dwz == $player->dwzPlayer->dwz) {
      $player->dwz = null;
    }
    if ($player->elo == $player->dwzPlayer->elo) {
      $player->elo = null;
    }

    $this->mainEntityManager->persist($player);
    $this->mainEntityManager->flush();
    return new JsonResponse();
  }

  private function getPlayers(string $tournament): array {
    $repo = $this->mainEntityManager->getRepository(Entity\PlayerRegistration::class);
    $players = $repo->findByTournament($tournament);
    return array_map(fn($p) => PlayerRegistration::fromEntity($p), $players);
  }
}
