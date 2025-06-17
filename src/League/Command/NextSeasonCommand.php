<?php

namespace Nsv\League\Command;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\LegacyUser;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'league:next-season',
  description: 'Creates the next season by copying all tournaments of the current season'
)]
class NextSeasonCommand extends Command
{
  function __construct(private EntityManagerInterface $leagueEntityManager, private LeagueRepository $leagueRepository) {
    parent::__construct();
  }

  protected function configure(): void {
    $this->addArgument('year', InputArgument::OPTIONAL, 'The year in which the new season should start', date('Y'));
    $this->addOption('commit', null, InputOption::VALUE_NONE, 'Use to actually commit changes instead of running in dry run mode');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $dryRun = !$input->getOption('commit');
    $year = $input->getArgument('year');

    foreach ($this->leagueRepository->findByYear($year - 1) as $league) {
      $this->cloneLeague($league, $output);
    }

    if ($dryRun) {
      $output->writeln('*** RUNNING IN DRY RUN MODE ***');
      $output->writeln('Use --commit option to actually persist changes');
    } else {
      $this->leagueEntityManager->flush();
      $output->writeln('New season created!');
    }

    return Command::SUCCESS;
  }

  private function cloneLeague(League $league, OutputInterface $output) {
    $output->writeln("Processing league: " . $league->name);

    $newLeague = clone $league;
    $newLeague->name = $this->processName($league->name, $league->year);
    $newLeague->path = $this->processName($league->path, $league->year);
    $newLeague->year = $league->year + 1;
    if ($league->registrationMinYearOfBirth) {
      $newLeague->registrationMinYearOfBirth = $league->registrationMinYearOfBirth + 1;
    } else {
      $newLeague->registrationMinYearOfBirth = null;
    }
    $output->writeln("  Creating league: " . $newLeague->name);

    $newLeague->manager = $this->cloneUser($league->manager, $output);
    $newLeague->divisions = [];
    $newLeague->teams = [];
    $newLeague->dates = [];

    $this->leagueEntityManager->detach($league);
    $this->leagueEntityManager->persist($newLeague);

    foreach ($league->divisions as $division) {
      $this->cloneDivision($division, $newLeague, $output);
    }
  }

  private function cloneDivision(Division $division, League $newLeague, OutputInterface $output) {
    $output->writeln("  Creating division: " . $division->name);

    $newDivision = clone $division;
    $newDivision->league = $newLeague;
    $newDivision->manager = $this->cloneUser($division->manager, $output);
    $newDivision->pairings = [];
    $newDivision->roundComments = [];

    $this->leagueEntityManager->detach($division);
    $this->leagueEntityManager->persist($newDivision);
  }

  private function cloneUser(LegacyUser $user, OutputInterface $output) {
    $output->writeln("    Creating user: " . $user->name);
    $newUser = clone $user;
    $this->leagueEntityManager->detach($user);
    $this->leagueEntityManager->persist($newUser);
    return $newUser;
  }

  /**
   * Modifies a league or division name for the next season.
   */
  // TODO: write tests.
  private function processName(string $name, int $lastYear) {
		return
			str_replace(substr($lastYear, 2, 2) . substr($lastYear+1, 2, 2), substr($lastYear+1, 2, 2) . substr($lastYear+2, 2, 2),
			str_replace($lastYear, $lastYear+1,
			str_replace($lastYear+1, $lastYear+2,
			str_replace("/".substr($lastYear+1, 2, 2), "/".substr($lastYear+2, 2, 2),
				$name
		))));
	}
}
