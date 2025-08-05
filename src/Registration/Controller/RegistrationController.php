<?php

namespace Nsv\Registration\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Repository\PlayerRepository;
use Nsv\Registration\Repository\PlayerRegistrationRepository;
use Nsv\Registration\Api\Model\PlayerRegistration;
use Nsv\Registration\Api\Model\TournamentConfig;
use Nsv\Registration\Entity as Entity;
use Nsv\WebApp\Core\ApiResponse;
use Nsv\WebApp\Core\WordPress\Auth;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/anmeldung/', name: 'registration_')]
class RegistrationController extends AbstractController {

  function __construct(
    private string $projectDir,
    private EntityManagerInterface $mainEntityManager,
    private PlayerRegistrationRepository $repository,
    private PlayerRepository $dwzRepository,
    private MailerInterface $mailer
  ) {}

  #[Route('{tournament}/', name: 'overview')]
  public function registration(string $tournament): Response {
    $config = $this->getConfig($tournament);
    return $this->render('@registration/registration.html.twig', [
      'title' => $config->tournamentName,
      'reg_config' => json_encode($config),
      'reg_players' => json_encode($this->getPlayers($config)),
      'is_manager' => $this->isManager($config)
    ]);
  }

  #[Route('api/{tournament}/players/', methods: ['GET'], name: 'players')]
  public function players(string $tournament): Response {
    $config = $this->getConfig($tournament);
    return new ApiResponse($this->getPlayers($config));
  }

  #[Route('api/{tournament}/players/', methods: ['POST'], name: 'players_register')]
  public function registerPlayer(string $tournament, #[MapRequestPayload] PlayerRegistration $request): Response {
    $config = $this->getConfig($tournament);

    $player = new Entity\PlayerRegistration();
    $player->tournament = $config->id;
    $this->populateEntity($request, $player);
 
    $this->mainEntityManager->persist($player);
    $this->mainEntityManager->flush();

    $this->sendConfirmationMail($config, $request, false);
    return new ApiResponse();
  }

  #[Route('api/{tournament}/players/{id}/', methods: ['PUT'], name: 'players_update')]
  public function updatePlayer(string $tournament, Entity\PlayerRegistration $registration, #[MapRequestPayload] PlayerRegistration $request): Response {
    $config = $this->getConfig($tournament);
    if (!$this->isManager($config) || $registration->tournament !== $config->id) {
      throw new AccessDeniedHttpException();
    }
    $waitlistConfirmed = !$request->waitlist && $registration->waitlist;

    $this->populateEntity($request, $registration);
    $this->mainEntityManager->persist($registration);
    $this->mainEntityManager->flush();

    if ($waitlistConfirmed) {
      $this->sendConfirmationMail($config, $request, true);
    }
    return new ApiResponse();
  }

  private function populateEntity(PlayerRegistration $request, Entity\PlayerRegistration $player): void {
    $player->group = $request->group;
    $player->waitlist = $request->waitlist ?? false;
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
    } else {
      // Delete a possible existing connection to the DWZ database.
      $player->dwzPlayer = null;
    }

    // Nulls here mean the latest data from the DWZ database should be used.
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
    $config = $this->getConfig($tournament);
    if (!$this->isManager($config) || $registration->tournament !== $config->id) {
      throw new AccessDeniedHttpException();
    }
    $this->mainEntityManager->remove($registration);
    $this->mainEntityManager->flush();
    return new JsonResponse();
  }

  private function getPlayers(TournamentConfig $config): array {
    $includeSensitive = $this->isManager($config);
    $players = $this->repository->findByTournament($config->id);
    if (!$includeSensitive) {
      $players = array_filter($players, fn(Entity\PlayerRegistration $p) => !$p->waitlist);
      $players = array_values($players);  // Re-index the array to fix JSON encoding.
    }
    return array_map(fn($p) => PlayerRegistration::fromEntity($p, $includeSensitive), $players);
  }

  private function isManager($config): bool {
    return Auth::isAdmin() || in_array(Auth::userName(), $config->managers);
  }

  private function getConfig(string $tournament): TournamentConfig {
    if (!preg_match('/^[a-z0-9-]+$/', $tournament)) {
      throw new BadRequestHttpException("Invalid tournament identifier");
    }
    $configFile = $this->projectDir . '/data/registration/' . $tournament . '.php';
    $config = require($configFile);
    if ($config instanceof TournamentConfig) {
      $config->id = $tournament;
      return $config;
    }
    throw new NotFoundHttpException("Tournament not found");
  }

  private function sendConfirmationMail(TournamentConfig $config, PlayerRegistration $player, bool $waitlistConfirmation): void {
    $email = (new TemplatedEmail())
      ->to($player->contactDetails->email)
      ->cc(...$config->emailCc)
      ->replyTo(...$config->emailReplyTo)
      ->subject("[" . ($player->waitlist ? 'Warteliste' : 'Anmeldung') .  " " . $config->tournamentName . "] " . $player->playerData->name)
      ->htmlTemplate('@registration/email/confirmation.html.twig')
      ->context([
        'config' => $config,
        'player' => $player,
        'waitlistConfirmation' => $waitlistConfirmation,
        'overviewUri' => $this->generateUrl('registration_overview', [
          'tournament' => $config->id,
        ], UrlGeneratorInterface::ABSOLUTE_URL)
      ]);
    $this->mailer->send($email);
  }
}
