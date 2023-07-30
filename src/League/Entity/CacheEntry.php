<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cache')]
class CacheEntry
{
  #[ORM\Id]
  #[ORM\ManyToOne(targetEntity: Division::class)]
  #[ORM\JoinColumn(name: "staffel", referencedColumnName: "id")]
  private Division $division;

  /**
   * Key used for arbitrary integer IDs, e.g. round ID or team ID.
   */
  #[ORM\Id]
  #[ORM\Column(name: 'runde')]
  private int $key;

  #[ORM\Id]
  #[ORM\Column(name: 'typ')]
  private string $type;

  #[ORM\ManyToOne(targetEntity: League::class)]
  #[ORM\JoinColumn(name: "turnier", referencedColumnName: "id")]
  private League $league;

  #[ORM\Column(name: 'inhalt')]
  private string $value;

  public function __call($property, $args) {
    return $this->$property;
  }

  public function __get($property) {
    return $this->$property;
  }

  public function __set($property, $value) {
    $this->$property = $value;
  }

  /**
   * Sorts first by date and then by round.
   */
  public static function compare(Date $a, Date $b) {
    if ($a->date == $b->date) return $a->round - $b->round;
    return $a->date < $b->date ? -1 : 1;
  }
}
