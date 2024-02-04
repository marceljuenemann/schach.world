<?php

namespace Nsv\League\Command;

use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'league:next-season',
  description: 'Creates the next season by copying all tournaments of the current season'
)]
class NextSeasonCommand extends Command
{
  function __construct(private LeagueRepository $leagueRepository) {
    parent::__construct();
  }

  protected function configure(): void {
    $this->addArgument('year', InputArgument::REQUIRED, 'The year in which the new season should start');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $year = $input->getArgument('year');
    $lastYear = $year - 1;
    $output->writeln('Hello NSV! ' . $year . ' ' . $lastYear);

    foreach ($this->leagueRepository->findByYear($lastYear) as $league) {
      $output->writeln($league->name);
    }





    return Command::SUCCESS;
  }
}
