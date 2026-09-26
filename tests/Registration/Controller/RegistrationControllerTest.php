<?php

namespace Tests\Registration\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Api\Model\PlayerData;
use Nsv\Registration\Api\Model\ContactDetails;
use Nsv\Registration\Api\Model\PlayerRegistration as ApiPlayerRegistration;
use Nsv\Registration\Controller\RegistrationController;
use Nsv\Registration\Entity\PlayerRegistration;
use Nsv\WebApp\Core\WordPress\Auth;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Mailer\MailerInterface;

// Tests are not rolled back (see CLAUDE.md), so every created registration must be cleaned up.
#[AllowMockObjectsWithoutExpectations]
class RegistrationControllerTest extends KernelTestCase
{
  private EntityManagerInterface $em;
  private MockObject $auth;
  private RegistrationController $controller;
  private array $createdIds = [];

  private const REGISTERED_NAME = 'Hiddenfield, Test';

  protected function setUp(): void {
    $container = static::getContainer();
    $this->auth = $this->createMock(Auth::class);
    $container->set(Auth::class, $this->auth);
    $container->set(MailerInterface::class, $this->createMock(MailerInterface::class));
    $this->controller = $container->get(RegistrationController::class);
    $this->em = $container->get(EntityManagerInterface::class);
  }

  protected function tearDown(): void {
    // Also catch registrations created via registerPlayer() that failed before being tracked.
    foreach ($this->em->getRepository(PlayerRegistration::class)->findBy(['name' => self::REGISTERED_NAME]) as $registration) {
      $this->createdIds[] = $registration->id;
    }
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

  public function testRegisterPlayer_dropsHiddenFieldsForNonManagers() {
    $this->auth->method('isAdmin')->willReturn(false);
    $this->auth->method('userName')->willReturn('someone-else');

    $this->controller->registerPlayer('test', $this->registrationRequest([
      'customField1' => 'sichtbar',
      'internalNotes' => 'eingeschleust',
    ]));

    $stored = $this->findStoredRegistration();
    $this->assertSame('sichtbar', $stored->additionalFields['customField1']);
    $this->assertArrayNotHasKey('internalNotes', $stored->additionalFields);
  }

  public function testRegisterPlayer_keepsHiddenFieldsForManagers() {
    $this->auth->method('isAdmin')->willReturn(true);

    $this->controller->registerPlayer('test', $this->registrationRequest([
      'internalNotes' => 'intern',
    ]));

    $this->assertSame('intern', $this->findStoredRegistration()->additionalFields['internalNotes']);
  }

  public function testRegisterPlayer_ignoresConfirmedForNonManagers() {
    $this->auth->method('isAdmin')->willReturn(false);
    $this->auth->method('userName')->willReturn('someone-else');
    $request = $this->registrationRequest([]);
    $request->confirmed = true;

    $this->controller->registerPlayer('test', $request);

    $this->assertFalse($this->findStoredRegistration()->confirmed);
  }

  public function testRegisterPlayer_managerCanSetConfirmed() {
    $this->auth->method('isAdmin')->willReturn(true);
    $request = $this->registrationRequest([]);
    $request->confirmed = true;

    $this->controller->registerPlayer('test', $request);

    $this->assertTrue($this->findStoredRegistration()->confirmed);
  }

  public function testUpdatePlayer_managerCanToggleConfirmed() {
    $this->auth->method('isAdmin')->willReturn(true);
    $this->controller->registerPlayer('test', $this->registrationRequest([]));
    $registration = $this->findStoredRegistration();

    $request = $this->registrationRequest([]);
    $request->confirmed = true;
    $this->controller->updatePlayer('test', $registration, $request);
    $this->assertTrue($this->findStoredRegistration()->confirmed);

    $request->confirmed = false;
    $this->controller->updatePlayer('test', $this->findStoredRegistration(), $request);
    $this->assertFalse($this->findStoredRegistration()->confirmed);
  }

  private function registrationRequest(array $additionalFields): ApiPlayerRegistration {
    $request = new ApiPlayerRegistration();
    $request->group = 'C';
    $request->playerData = new PlayerData();
    $request->playerData->name = self::REGISTERED_NAME;
    $request->playerData->club = 'Testverein';
    $request->playerData->gender = 'm';
    $request->playerData->yearOfBirth = 2000;
    $request->playerData->dwz = 1200;
    $request->playerData->elo = null;
    $request->playerData->fideTitle = null;
    $request->playerData->fideId = null;
    $request->playerData->zps = null;
    $request->playerData->memberId = null;
    $request->contactDetails = new ContactDetails();
    $request->contactDetails->name = 'Test';
    $request->contactDetails->email = 'test@example.com';
    $request->additionalFields = $additionalFields;
    return $request;
  }

  private function findStoredRegistration(): PlayerRegistration {
    $this->em->clear();
    return $this->em->getRepository(PlayerRegistration::class)->findOneBy(['tournament' => 'test', 'name' => self::REGISTERED_NAME]);
  }

  private function playerNames(string $jsonContent): array {
    $players = json_decode($jsonContent, true);
    return array_column(array_column($players, 'playerData'), 'name');
  }
}
