<?php

namespace Tests\WebApp\Controller;

use Doctrine\DBAL\Connection;
use Nsv\WebApp\Core\WordPress\Auth;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Mailer\MailerInterface;

require_once __DIR__ . '/../../Support/wordpress-stubs.php';

// Tests are not rolled back (see CLAUDE.md), so all created events are deleted in tearDown().
#[AllowMockObjectsWithoutExpectations]
class CalendarControllerTest extends WebTestCase
{
  private const EVENT_NAME = 'PHPUnit Testtermin';

  private KernelBrowser $client;
  private Connection $db;
  private MockObject $auth;
  private MockObject $mailer;

  protected function setUp(): void {
    $this->client = static::createClient();
    $container = static::getContainer();
    $this->db = $container->get('doctrine.dbal.main_connection');
    $this->auth = $this->createMock(Auth::class);
    $this->auth->method('loginRedirect')->willReturnCallback(fn($uri) => '/wp-login.php?redirect_to=' . urlencode($uri));
    $container->set(Auth::class, $this->auth);
    $this->mailer = $this->createMock(MailerInterface::class);
    $container->set(MailerInterface::class, $this->mailer);
  }

  protected function tearDown(): void {
    $this->db->executeStatement('DELETE FROM termine2 WHERE name LIKE ?', [self::EVENT_NAME . '%']);
    parent::tearDown();
  }

  private function insertEvent(bool $approved, string $date = '2050-06-01'): int {
    $this->db->insert('termine2', [
      'name' => self::EVENT_NAME,
      'url' => 'https://example.com/termin',
      'date' => $date,
      'is_nsv' => 0,
      'is_approved' => $approved ? 1 : 0,
    ]);
    return (int) $this->db->lastInsertId();
  }

  private function event(): array|false {
    return $this->db->fetchAssociative('SELECT * FROM termine2 WHERE name = ?', [self::EVENT_NAME]);
  }

  public function testCalendar_showsOnlyUpcomingApprovedEvents() {
    $this->insertEvent(approved: false);
    $this->db->insert('termine2', ['name' => self::EVENT_NAME . ' (vergangen)', 'url' => '', 'date' => '2001-01-01', 'is_nsv' => 0, 'is_approved' => 1]);

    $crawler = $this->client->request('GET', '/termine/');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h2', 'Terminkalender');
    $this->assertSelectorExists('a.fw-bold:contains("Test NSV")');
    $this->assertStringNotContainsString(self::EVENT_NAME, $crawler->html());
  }

  public function testAddEntry_formForGuests() {
    $this->client->request('GET', '/termine/eintragen/');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorExists('input[name="form[captcha]"]');
    $this->assertSelectorNotExists('input[name="form[isNsv]"]');
  }

  public function testAddEntry_formForAuthors() {
    $this->auth->method('isAuthor')->willReturn(true);
    $this->client->request('GET', '/termine/eintragen/');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorExists('input[name="form[isNsv]"]');
    $this->assertSelectorNotExists('input[name="form[captcha]"]');
  }

  public function testAddEntry_guestSubmissionNeedsApproval() {
    $this->auth->method('isAuthor')->willReturn(false);
    $this->mailer->expects($this->once())->method('send')->with($this->callback(function (TemplatedEmail $email) {
      $this->assertSame('[NSV Terminkalender] ' . self::EVENT_NAME, $email->getSubject());
      $this->assertSame($_ENV['CALENDAR_APPROVER'], $email->getTo()[0]->getAddress());
      $this->assertMatchesRegularExpression('#/termine/approve/\d+/$#', $email->getContext()['approvalUrl']);
      return true;
    }));

    $this->client->request('POST', '/termine/eintragen/', ['form' => [
      'date' => '2050-06-01',
      'name' => self::EVENT_NAME,
      'url' => 'https://example.com/termin',
      'captcha' => '64',
    ]]);

    $this->assertResponseRedirects('/termine/');
    $event = $this->event();
    $this->assertSame('2050-06-01', $event['date']);
    $this->assertSame(0, (int) $event['is_approved']);

    $this->client->followRedirect();
    $this->assertSelectorTextContains('body', 'Der Termin wird nach Freischaltung');
  }

  public function testAddEntry_wrongCaptcha() {
    $this->mailer->expects($this->never())->method('send');

    $this->client->request('POST', '/termine/eintragen/', ['form' => [
      'date' => '2050-06-01',
      'name' => self::EVENT_NAME,
      'url' => 'https://example.com/termin',
      'captcha' => '42',
    ]]);

    $this->assertResponseStatusCodeSame(422);
    $this->assertFalse($this->event());
  }

  public function testAddEntry_dateInThePast() {
    $this->client->request('POST', '/termine/eintragen/', ['form' => [
      'date' => '2001-01-01',
      'name' => self::EVENT_NAME,
      'url' => 'https://example.com/termin',
      'captcha' => '64',
    ]]);

    $this->assertResponseStatusCodeSame(422);
    $this->assertFalse($this->event());
  }

  public function testAddEntry_authorSubmissionIsApprovedImmediately() {
    $this->auth->method('isAuthor')->willReturn(true);
    $this->mailer->expects($this->never())->method('send');

    $this->client->request('POST', '/termine/eintragen/', ['form' => [
      'date' => '2050-06-01',
      'name' => self::EVENT_NAME,
      // TODO: The link is optional for authors, but omitting it currently fails because
      // termine2.url is NOT NULL.
      'url' => 'https://example.com/termin',
      'isNsv' => '1',
    ]]);

    $this->assertResponseRedirects('/termine/');
    $event = $this->event();
    $this->assertSame(1, (int) $event['is_approved']);
    $this->assertSame(1, (int) $event['is_nsv']);

    $this->client->followRedirect();
    $this->assertSelectorTextContains('body', 'Termin erfolgreich eingetragen');
  }

  public function testApprove_unknownEvent() {
    $this->client->request('GET', '/termine/approve/999999999/');
    $this->assertResponseStatusCodeSame(404);
  }

  public function testApprove_redirectsToLogin() {
    $id = $this->insertEvent(approved: false);
    $this->client->request('GET', "/termine/approve/$id/");

    $this->assertResponseRedirects('/wp-login.php?redirect_to=' . urlencode("http://localhost/termine/approve/$id/"));
    $this->assertSame(0, (int) $this->event()['is_approved']);
  }

  public function testApprove_asAuthor() {
    $this->auth->method('isLoggedIn')->willReturn(true);
    $this->auth->method('isAuthor')->willReturn(true);
    $id = $this->insertEvent(approved: false);
    $this->client->request('GET', "/termine/approve/$id/");

    $this->assertResponseRedirects('/termine/');
    $this->assertSame(1, (int) $this->event()['is_approved']);
  }

  public function testApprove_deniedForNonAuthors() {
    $this->auth->method('isLoggedIn')->willReturn(true);
    $this->auth->method('isAuthor')->willReturn(false);
    $id = $this->insertEvent(approved: false);
    $this->client->request('GET', "/termine/approve/$id/");

    $this->assertResponseRedirects('/termine/');
    $this->assertSame(0, (int) $this->event()['is_approved']);
  }
}
