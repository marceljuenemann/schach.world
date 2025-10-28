<?php
namespace Nsv\Util\Feed;

use DateTimeInterface;

final class Article
{
  public function __construct(
    public string $url,
    public string $title,
    public DateTimeInterface $date,
  ) {}
}
