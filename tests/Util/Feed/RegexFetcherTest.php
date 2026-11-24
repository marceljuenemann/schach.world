<?php

namespace Tests\Util\Feed;

use Nsv\Util\Feed\RegexFetcher;
use PHPUnit\Framework\TestCase;

class RegexFetcherTest extends TestCase
{
  private const FIXTURE = __DIR__ . '/fixtures/news.html';
  private const PATTERN = '/<td><h3>([^<]*)<\/h3><\/td>\s*<td align="right"><h3>([^<]*)<\/h3><\/td>/i';

  public function testFetch() {
    $before = new \DateTime();
    $fetcher = new RegexFetcher(url: self::FIXTURE, pattern: self::PATTERN);
    $articles = iterator_to_array($fetcher->fetch('(4)'));

    // The match with an empty title is skipped.
    $this->assertSame(['Bezirksmeisterschaft & Pokal', 'Neuer Vorstand gewählt', 'Ohne Datum'], array_map(fn($a) => $a->title, $articles));
    $this->assertSame('(4)', $articles[0]->provider);
    $this->assertSame(self::FIXTURE, $articles[0]->url);
    $this->assertSame('2026-11-01 00:00:00', $articles[0]->date->format('Y-m-d H:i:s'));
    $this->assertSame('2026-10-15 00:00:00', $articles[1]->date->format('Y-m-d H:i:s'));
    // Unparseable dates fall back to now.
    $this->assertGreaterThanOrEqual($before, $articles[2]->date);
  }

  public function testFetch_customDateGroupAndFormat() {
    $fetcher = new RegexFetcher(
      url: self::FIXTURE,
      pattern: '/<h3>([^<]+)<\/h3><\/td>\s*<td align="right"><h3>(\d\d)\.(\d\d)\.(\d{4})<\/h3>/',
      dateGroup: 4,
      dateFormat: 'Y',
    );
    $articles = iterator_to_array($fetcher->fetch('X'));

    $this->assertSame(['Bezirksmeisterschaft & Pokal', 'Neuer Vorstand gewählt'], array_map(fn($a) => $a->title, $articles));
    $this->assertSame('2026 00:00:00', $articles[0]->date->format('Y H:i:s'));
  }

  public function testFetch_noMatches() {
    $fetcher = new RegexFetcher(url: self::FIXTURE, pattern: '/<marquee>(.*)<\/marquee>/');
    $this->assertSame([], iterator_to_array($fetcher->fetch('X')));
  }

  public function testFetch_unreachableUrl() {
    $fetcher = new RegexFetcher(url: __DIR__ . '/fixtures/does-not-exist.html', pattern: self::PATTERN);
    $this->assertSame([], iterator_to_array($fetcher->fetch('X')));
  }
}
