<?php
namespace Nsv\Util\Feed;

use DateTime;

final class RssFetcher implements ArticleFetcher
{
  public function __construct(
    private readonly string $url,
  ) {}

  public function fetch(): iterable
  {
    $xmlContent = file_get_contents($this->url);
    if ($xmlContent === false) {
      return;
    }

    $xml = simplexml_load_string($xmlContent);
    if ($xml === false) {
      return;
    }

    foreach ($xml->channel->item as $item) {
      $title = (string) $item->title;
      $link = (string) $item->link;
      $pubDate = (string) $item->pubDate;

      if (empty($title) || empty($link)) {
        continue;
      }

      $date = DateTime::createFromFormat(DateTime::RSS, $pubDate);
      if ($date === false) {
        $date = new DateTime();
      }

      yield new Article(
        url: $link,
        title: $title,
        date: $date,
      );
    }
  }
}