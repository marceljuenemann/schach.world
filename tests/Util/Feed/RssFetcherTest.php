<?php

namespace Tests\Util\Feed;

use Nsv\Util\Feed\Article;
use Nsv\Util\Feed\RssFetcher;
use PHPUnit\Framework\TestCase;

class RssFetcherTest extends TestCase
{
  public function testFetch() {
    $before = new \DateTime();
    $articles = iterator_to_array((new RssFetcher(__DIR__ . '/fixtures/feed.rss'))->fetch('DSB'));

    // The item without a link is skipped.
    $this->assertCount(2, $articles);
    $this->assertContainsOnlyInstancesOf(Article::class, $articles);

    $this->assertSame('DSB', $articles[0]->provider);
    $this->assertSame('Landesmeisterschaft & Jugend', $articles[0]->title);
    $this->assertSame('https://example.com/artikel-1', $articles[0]->url);
    $this->assertSame('2026-11-13 10:00:00 +01:00', $articles[0]->date->format('Y-m-d H:i:s P'));

    // Unparseable dates fall back to now.
    $this->assertSame('Artikel mit kaputtem Datum', $articles[1]->title);
    $this->assertGreaterThanOrEqual($before, $articles[1]->date);
  }

  public function testFetch_invalidXml() {
    $articles = @iterator_to_array((new RssFetcher(__DIR__ . '/fixtures/invalid.rss'))->fetch('DSB'));
    $this->assertSame([], $articles);
  }
}
