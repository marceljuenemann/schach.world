<?php

namespace Nsv\WebApp\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nsv\Util\Feed\RssFetcher;

#[AsCommand(name: 'nsv:headlines')]
class HeadlinesCommand extends Command
{
  function __construct(private string $projectDir) {
    parent::__construct();
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln('Fetching headlines from DSB feed...');

    $fetcher = new RssFetcher('https://www.schachbund.de/share/dsb-feed.xml');
    
    foreach ($fetcher->fetch() as $article) {
      $output->writeln($article->date->format('Y-m-d') . ' - ' . $article->title);
    }

    return Command::SUCCESS;
  }
}
