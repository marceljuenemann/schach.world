<?php
namespace Nsv\Util\Feed;

interface ArticleFetcher
{
  /**
   * Fetches articles from the source.
   *
   * @return iterable<Article>
   */
  public function fetch(string $provider): iterable;
}
