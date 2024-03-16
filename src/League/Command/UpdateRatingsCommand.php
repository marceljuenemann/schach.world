<?php

namespace Nsv\League\Command;

use Nsv\League\Core\LegacySystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
  name: 'league:update-ratings',
  description: 'Updates ratings of players for the current season'
)]
class UpdateRatingsCommand extends Command
{
  function __construct(private LegacySystem $legacySystem) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $this->legacySystem->initialize();

    $buffered = $this->legacySystem->invokeAdminScript('DwzUpdate');
    $buffered = preg_replace('/\<br(\s*)?\/?\>/i', "\n", $buffered);
    
    // TODO: log to league.log
    // TODO: cron script
    $output->writeln($buffered);

    return Command::SUCCESS;
  }
}
