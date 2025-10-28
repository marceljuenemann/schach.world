<?php

namespace Nsv\WebApp\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Nsv\Util\Feed\RssFetcher;

/**
 * TODO:
 * - Limit number of articles per provider
 * - Other districts
 * - Log or mail errors
 * - Render as HTML
 */
#[AsCommand(name: 'nsv:headlines')]
class HeadlinesCommand extends Command
{
  private array $providers;

  function __construct(private string $projectDir) {
    parent::__construct();
    $this->providers = [
      'DSB' => new RssFetcher('https://www.schachbund.de/share/dsb-feed.xml'),
      'NSJ' => new RssFetcher('http://www.nsj-online.de/wordpress/feed/'),
      '(1)' => new RssFetcher('https://schachbezirk-hannover.de/feed/'),
      '(3)' => new RssFetcher('https://www.schachbezirk3.de/rss'),
      //'Bezirk4' => new RssFetcher('https://www.schachbezirk4.de/rss'),
      '(5)' => new RssFetcher('http://sboo.de/index.php?format=feed&type=rss'),
    ];
  }

  protected function configure(): void
  {
    $this->addArgument('provider', InputArgument::OPTIONAL, 'Specific provider to fetch from (DSB, NSJ, (1), ...)');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $provider = $input->getArgument('provider');

    foreach ($this->fetchArticles($provider) as $article) {
      $output->writeln($article->date->format('Y-m-d') . ' - ' . $article->title);
    }

    return Command::SUCCESS;
  }

  /**
   * Fetches articles, applying the following limits for each provider:
   * - At most 3 articles
   * - At most one articles older than 7 days
   * - No articles older than 30 days
   */
  private function fetchArticles(?string $providerName): iterable
  {
    $oneWeekAgo = (new \DateTime())->modify('-7 days');
    $thirtyDaysAgo = (new \DateTime())->modify('-30 days');

    foreach ($this->providers as $name => $fetcher) {
      if ($providerName !== null && $name !== $providerName) {
        continue;
      }

      $articleCount = 0;
      foreach ($fetcher->fetch() as $article) {
        if ($article->date < $thirtyDaysAgo) {
          break;
        }

        yield $article;
        $articleCount++;

        if ($article->date < $oneWeekAgo || $articleCount >= 3) {
          break;
        }
      }
    }
  }
}
