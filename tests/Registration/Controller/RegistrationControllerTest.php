<?php

namespace Tests\Registration\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Registration\Controller\RegistrationController;
use Nsv\Registration\Entity\PlayerRegistration;
use Nsv\WebApp\Core\WordPress\Auth;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

// `registration_players` is InnoDB (unlike League's MyISAM tables), so Dama's per-test
// transaction rollback is presumed to apply here - but no other test in this repo exercises
// an InnoDB table under the `main` entity manager, so that's unverified. Clean up explicitly
// (like League tests do) rather than relying on it, until it's proven to work reliably.
#[AllowMockObjectsWithoutExpectations]
class RegistrationControllerTest extends KernelTestCase
{
  private EntityManagerInterface $em;
  private MockObject $auth;
  private RegistrationController $controller;
  private array $createdIds = [];

  protected function setUp(): void {
    $container = static::getContainer();
    $this->auth = $this->createMock(Auth::class);
    $container->set(Auth::class, $this->auth);
    $this->controller = $container->get(RegistrationController::class);
    $this->em = $container->get(EntityManagerInterface::class);
  }

  protected function tearDown(): void {
    foreach ($this->createdIds as $id) {
      $registration = $this->em->find(PlayerRegistration::class, $id);
      if ($registration) {
        $this->em->remove($registration);
      }
    }
    $this->em->flush();
    parent::tearDown();
  }

  private function createRegistration(array $overrides = []): PlayerRegistration {
    $registration = new PlayerRegistration();
    $registration->tournament = 'test';
    $registration->group = 'A';
    $registration->waitlist = false;
    $registration->name = 'Mustermann, Max';
    $registration->club = 'Testverein';
    $registration->gender = 'm';
    $registration->yearOfBirth = 2000;
    $registration->dwz = 1500;
    $registration->elo = null;
    $registration->fideTitle = null;
    $registration->fideId = null;
    $registration->contactName = 'Max Mustermann';
    $registration->contactEMail = 'max@example.com';
    $registration->created = new \DateTimeImmutable();
    foreach ($overrides as $key => $value) {
      $registration->$key = $value;
    }
    $this->em->persist($registration);
    $this->em->flush();
    $this->createdIds[] = $registration->id;
    return $registration;
  }

  public function testDeletePlayer_setsUnregisteredAtInsteadOfDeletingTheRow() {
    $this->auth->method('isAdmin')->willReturn(true);
    $registration = $this->createRegistration();

    $this->controller->delete_player('test', $registration);

    $this->em->clear();
    $reloaded = $this->em->find(PlayerRegistration::class, $registration->id);
    $this->assertNotNull($reloaded, 'The row should still exist in the database.');
    $this->assertNotNull($reloaded->unregisteredAt);
  }

  public function testDeletePlayer_deniedForNonManager() {
    $this->auth->method('isAdmin')->willReturn(false);
    $this->auth->method('userName')->willReturn('someone-else');
    $registration = $this->createRegistration();

    $this->expectException(AccessDeniedHttpException::class);
    $this->controller->delete_player('test', $registration);
  }

  public function testPlayers_hidesUnregisteredFromNonManagers() {
    $this->auth->method('isAdmin')->willReturn(false);
    $this->auth->method('userName')->willReturn('someone-else');
    $this->createRegistration(['name' => 'Aktiv, Anna']);
    $this->createRegistration(['name' => 'Storniert, Sina', 'unregisteredAt' => new \DateTimeImmutable()]);

    $response = $this->controller->players('test');
    $names = $this->playerNames($response->getContent());

    $this->assertContains('Aktiv, Anna', $names);
    $this->assertNotContains('Storniert, Sina', $names);
  }

  public function testPlayers_showsUnregisteredToManagers() {
    $this->auth->method('isAdmin')->willReturn(true);
    $this->createRegistration(['name' => 'Aktiv, Anna']);
    $this->createRegistration(['name' => 'Storniert, Sina', 'unregisteredAt' => new \DateTimeImmutable()]);

    $response = $this->controller->players('test');
    $names = $this->playerNames($response->getContent());

    $this->assertContains('Aktiv, Anna', $names);
    $this->assertContains('Storniert, Sina', $names);
  }

  private function playerNames(string $jsonContent): array {
    $players = json_decode($jsonContent, true);
    return array_column(array_column($players, 'playerData'), 'name');
  }
}
