<?php

namespace Nsv\League\Command;

use Nsv\League\Core\LegacySystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'league:send-result-links',
  description: 'Sends eMails to team leaders with a link for entering results'
)]
class SendResultLinksCommand extends Command
{
  function __construct(
    private LoggerInterface $leagueLogger,
    private LegacySystem $legacySystem
  ) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->legacySystem->initialize();

    $buffered = $this->legacySystem->invokeAdminScript('Eingabelinks');
    
    $output->writeln($buffered);
    $this->leagueLogger->info('league:send-result-links: ' . $buffered);

    return Command::SUCCESS;
  }
}
