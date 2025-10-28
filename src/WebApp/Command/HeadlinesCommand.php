<?php

namespace Nsv\WebApp\Command;

use Nsv\Util\Feed\RegexFetcher;
use Nsv\Util\Feed\RssFetcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO:
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
      /*
      '(2)' => new RegexFetcher(
        url: 'https://www.schachbezirk-braunschweig.de/',
        pattern: '/<h1[^>]*>([^<]+)<\/h1>/is',
      ),
      */
      '(3)' => new RssFetcher('https://www.schachbezirk3.de/rss'),
      '(4)' => new RegexFetcher(
        url: 'http://schachbezirk4.de/b4_home.php',
        pattern: '/<td><h3>([^<]+)<\/h3><\/td>[\s]*<td align="right"><h3>(\d\d\.\d\d\.\d\d\d\d)<\/h3><\/td>/i',
        titleGroup: 1,
        dateGroup: 2,
        dateFormat: 'd.m.Y'
      ),
      '(5)' => new RssFetcher('http://sboo.de/index.php?format=feed&type=rss'),
      '(6)' => new RegexFetcher(
        url: 'http://www.schachbezirk-osnabrueck-emsland.de/news/index.php?rubrik=1',
        pattern: '/<h3[^>]*><a[^>]*>([^<]+)<\/a><\/h3><p class=\"vorschau\">(\d\d\.\d\d\.\d\d\d\d):/i',
        titleGroup: 1,
        dateGroup: 2,
        dateFormat: 'd.m.Y'
      ),      
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
