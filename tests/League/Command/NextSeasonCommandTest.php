<?php

namespace Tests\League\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\LegacyUser;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Only tests the dry run mode, which doesn't flush anything to the database.
 * Never run this command with --commit in tests.
 */
class NextSeasonCommandTest extends KernelTestCase
{
  private EntityManagerInterface $em;

  protected function setUp(): void {
    self::bootKernel();
    $this->em = static::getContainer()->get('doctrine.orm.league_entity_manager');
  }

  protected function tearDown(): void {
    // Discard everything the command scheduled for insertion.
    $this->em->clear();
    parent::tearDown();
  }

  /** @return object[] */
  private function scheduledInsertions(string $class): array {
    return array_values(array_filter(
      $this->em->getUnitOfWork()->getScheduledEntityInsertions(),
      fn($entity) => $entity instanceof $class
    ));
  }

  public function testDryRun() {
    $leagueCount = (int) $this->em->getConnection()->fetchOne('SELECT COUNT(*) FROM turniere');

    $tester = new CommandTester((new Application(self::$kernel))->find('league:next-season'));
    $tester->execute(['year' => '2026']);

    $tester->assertCommandIsSuccessful();
    $output = $tester->getDisplay();
    $this->assertStringContainsString('Processing league: Landes- und Verbandsligen 2025/26', $output);
    $this->assertStringContainsString('  Creating league: Landes- und Verbandsligen 2026/27', $output);
    $this->assertStringContainsString('  Creating division: Landesliga Nord', $output);
    $this->assertStringContainsString('*** RUNNING IN DRY RUN MODE ***', $output);
    $this->assertStringNotContainsString('New season created!', $output);

    // The new entities are only scheduled, not written.
    $this->assertSame($leagueCount, (int) $this->em->getConnection()->fetchOne('SELECT COUNT(*) FROM turniere'));

    $leagues = $this->scheduledInsertions(League::class);
    $newLeague = array_values(array_filter($leagues, fn(League $league) => $league->path === 'nsv-2627'))[0];
    $this->assertSame('Landes- und Verbandsligen 2026/27', $newLeague->name);
    $this->assertSame(2026, $newLeague->year);
    $this->assertSame([], $newLeague->teams);
    $this->assertSame([], $newLeague->dates);
    $this->assertContainsOnlyInstancesOf(LegacyUser::class, $this->scheduledInsertions(LegacyUser::class));

    $divisions = array_filter($this->scheduledInsertions(Division::class), fn(Division $d) => $d->league === $newLeague);
    $this->assertContains('Landesliga Nord', array_map(fn(Division $d) => $d->name, $divisions));
    foreach ($divisions as $division) {
      $this->assertSame([], $division->pairings);
      $this->assertNotSame($newLeague->manager, $division->manager);
    }
  }

  public function testDryRun_noLeaguesInPreviousYear() {
    $tester = new CommandTester((new Application(self::$kernel))->find('league:next-season'));
    $tester->execute(['year' => '1990']);

    $tester->assertCommandIsSuccessful();
    $this->assertStringStartsWith('*** RUNNING IN DRY RUN MODE ***', $tester->getDisplay());
    $this->assertSame([], $this->scheduledInsertions(League::class));
  }
}
