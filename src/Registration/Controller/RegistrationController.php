<?php

namespace Nsv\Registration\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Repository\PlayerRepository;
use Nsv\Registration\Repository\PlayerRegistrationRepository;
use Nsv\Registration\Api\Model\PlayerRegistration;
use Nsv\Registration\Entity as Entity;
use Nsv\WebApp\Core\ApiResponse;
use Nsv\WebApp\Core\WordPress\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

// See /ng/src/registration/types.ts for schema.
const TEST_CONFIG = [
  'id' => 'test',
  'tournamentName' => 'Testturnier 2024',
  'managers' => ['marcel', 'beni'],
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
    private PlayerRegistrationRepository $repository,
    private PlayerRepository $dwzRepository
  ) {}

  #[Route('{tournament}/', name: 'registration')]
  public function registration(string $tournament): Response {
    return $this->render('@registration/registration.html.twig', [
      'title' => TEST_CONFIG['tournamentName'],
      'reg_config' => json_encode(TEST_CONFIG),
      'reg_players' => json_encode($this->getPlayers($tournament)),
      'is_manager' => $this->isManager(TEST_CONFIG)
    ]);
  }

  #[Route('api/{tournament}/players/', methods: ['GET'], name: 'players')]
  public function players(string $tournament): Response {
    return new ApiResponse($this->getPlayers($tournament));
  }

  #[Route('api/{tournament}/players/', methods: ['POST'], name: 'players_register')]
  public function registerPlayer(string $tournament, #[MapRequestPayload] PlayerRegistration $request): Response {
    $player = new Entity\PlayerRegistration();
    $player->tournament = $tournament;
    $this->populateEntity($request, $player);
 
    $this->mainEntityManager->persist($player);
    $this->mainEntityManager->flush();
    return new ApiResponse();
  }

  #[Route('api/{tournament}/players/{id}/', methods: ['PUT'], name: 'players_update')]
  public function updatePlayer(string $tournament, Entity\PlayerRegistration $registration, #[MapRequestPayload] PlayerRegistration $request): Response {
    if (!$this->isManager(TEST_CONFIG) || $registration->tournament !== $tournament) {
      throw new AccessDeniedHttpException();
    }
    $this->populateEntity($request, $registration);
    $this->mainEntityManager->persist($registration);
    $this->mainEntityManager->flush();
    return new ApiResponse();
  }

  private function populateEntity(PlayerRegistration $request, Entity\PlayerRegistration $player): void {
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
    if ($player->club == $player->dwzPlayer?->club?->name) {
      $player->club = null;
    }
    if ($player->dwz == $player->dwzPlayer?->dwz) {
      $player->dwz = null;
    }
    if ($player->elo == $player->dwzPlayer?->elo) {
      $player->elo = null;
    }
  }

  #[Route('api/{tournament}/players/{id}/', methods: 'DELETE', name: 'delete_player')]
  public function delete_player(string $tournament, Entity\PlayerRegistration $registration): Response {
    if (!$this->isManager(TEST_CONFIG) || $registration->tournament !== $tournament) {
      throw new AccessDeniedHttpException();
    }
    $this->mainEntityManager->remove($registration);
    $this->mainEntityManager->flush();
    return new JsonResponse();
  }

  private function getPlayers(string $tournament): array {
    $includeSensitive = $this->isManager(TEST_CONFIG);
    $players = $this->repository->findByTournament($tournament);
    return array_map(fn($p) => PlayerRegistration::fromEntity($p, $includeSensitive), $players);
  }

  private function isManager($config): bool {
    return Auth::isAdmin() || in_array(Auth::userName(), $config['managers']);
  }
}
