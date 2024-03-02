<?php

namespace Nsv\WebApp\Command;

use Nsv\WebApp\Controller\ClubController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nsv:cache:clear')]
class CacheClearCommand extends Command
{
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln('Clearing NSV cache...');
    $cache = new FilesystemAdapter(ClubController::CACHE_NAMESPACE);
    $cache->deleteItem(ClubController::CACHE_KEY);
    return Command::SUCCESS;
 }
}
