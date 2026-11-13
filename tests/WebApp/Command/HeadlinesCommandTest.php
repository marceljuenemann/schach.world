<?php

namespace Tests\WebApp\Command;

use Nsv\Util\Feed\Article;
use Nsv\Util\Feed\ArticleFetcher;
use Nsv\WebApp\Command\HeadlinesCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class HeadlinesCommandTest extends TestCase
{
  private string $projectDir;

  protected function setUp(): void {
    $this->projectDir = sys_get_temp_dir() . '/headlines-test-' . bin2hex(random_bytes(4));
    mkdir($this->projectDir . '/data/headlines', recursive: true);
  }

  protected function tearDown(): void {
    @unlink($this->projectDir . '/data/headlines/headlines.html');
    rmdir($this->projectDir . '/data/headlines');
    rmdir($this->projectDir . '/data');
    rmdir($this->projectDir);
  }

  /**
   * Returns a fetcher that yields one article per given age (in days), newest first.
   */
  private function fetcher(string $prefix, array $agesInDays): ArticleFetcher {
    return new class($prefix, $agesInDays) implements ArticleFetcher {
      public function __construct(private string $prefix, private array $ages) {}

      public function fetch(string $provider): iterable {
        foreach ($this->ages as $age) {
          yield new Article(
            provider: $provider,
            url: "https://example.com/{$this->prefix}-$age",
            title: "{$this->prefix} vor $age Tagen",
            date: (new \DateTime())->setTime(12, 0)->modify("-$age days"),
          );
        }
      }
    };
  }

  private function execute(array $providers, array $input = []): CommandTester {
    $tester = new CommandTester(new HeadlinesCommand($this->projectDir, $providers));
    $tester->execute($input);
    $tester->assertCommandIsSuccessful();
    return $tester;
  }

  private function titles(CommandTester $tester): array {
    $lines = array_filter(explode("\n", trim($tester->getDisplay())));
    return array_map(fn($line) => explode(' - ', $line)[2], $lines);
  }

  public function testLimitsArticlesPerProvider() {
    $tester = $this->execute([
      // At most 3 articles.
      'A' => $this->fetcher('A', [0, 1, 2, 3]),
      // Stops after the first article older than 7 days.
      'B' => $this->fetcher('B', [5, 10, 11]),
      // No articles older than 30 days.
      'C' => $this->fetcher('C', [31, 40]),
    ]);

    // Sorted by date, newest first.
    $this->assertSame(
      ['A vor 0 Tagen', 'A vor 1 Tagen', 'A vor 2 Tagen', 'B vor 5 Tagen', 'B vor 10 Tagen'],
      $this->titles($tester)
    );
    $this->assertStringStartsWith('A - ' . date('Y-m-d') . ' - A vor 0 Tagen', $tester->getDisplay());
    $this->assertFileDoesNotExist($this->projectDir . '/data/headlines/headlines.html');
  }

  public function testSingleProvider() {
    $tester = $this->execute([
      'A' => $this->fetcher('A', [0]),
      'B' => $this->fetcher('B', [1]),
    ], ['provider' => 'B']);

    $this->assertSame(['B vor 1 Tagen'], $this->titles($tester));
  }

  public function testRender() {
    $this->execute([
      'A' => $this->fetcher('A&B', [2]),
      'B' => $this->fetcher('<b>', [1]),
    ], ['--render' => true]);

    $this->assertSame(
      "<table cellpadding='0'>"
        . "<tr><td style='font-size: smaller'>B</td><td style='padding-bottom: 3px'><a href='https://example.com/&lt;b&gt;-1'>&lt;b&gt; vor 1 Tagen</a></td></tr>"
        . "<tr><td style='font-size: smaller'>A</td><td style='padding-bottom: 3px'><a href='https://example.com/A&amp;B-2'>A&amp;B vor 2 Tagen</a></td></tr>"
        . "</table>",
      file_get_contents($this->projectDir . '/data/headlines/headlines.html')
    );
  }

  public function testDefaultProviders() {
    $command = new HeadlinesCommand($this->projectDir);
    $providers = (new \ReflectionProperty($command, 'providers'))->getValue($command);
    $this->assertContains('DSB', array_keys($providers));
    $this->assertContainsOnlyInstancesOf(ArticleFetcher::class, $providers);
  }
}
