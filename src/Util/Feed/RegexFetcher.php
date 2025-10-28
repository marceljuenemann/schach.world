<?php
namespace Nsv\Util\Feed;

use DateTime;

final class RegexFetcher implements ArticleFetcher
{
  public function __construct(
    private string $url,
    private string $pattern,
    private int $titleGroup = 1,
    private int $dateGroup = 2,
    private string $dateFormat = 'd.m.Y',
  ) {}

  public function fetch(): iterable
  {
    $html = @file_get_contents($this->url);
    if ($html === false) {
      return;
    }

    $matches = [];
    if (!preg_match_all($this->pattern, $html, $matches, PREG_SET_ORDER)) {
      return;
    }

    foreach ($matches as $match) {
      $title = $match[$this->titleGroup] ?? null;
      $dateString = $match[$this->dateGroup] ?? null;

      if (empty($title)) {
        continue;
      }

      // Decode HTML entities to avoid double-escaping
      $title = html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

      // Parse the date
      $date = null;
      if (!empty($dateString)) {
        $date = DateTime::createFromFormat($this->dateFormat, $dateString);
        if ($date !== false) {
          $date->setTime(0, 0, 0);
        }
      }
      
      if ($date === false || $date === null) {
        $date = new DateTime();
      }

      yield new Article(
        url: $this->url,
        title: $title,
        date: $date,
      );
    }
  }
}
