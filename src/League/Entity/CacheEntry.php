<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'cache')]
class CacheEntry
{
  const TYPE_MATCH_DAY = 'MatchDay';

  #[ORM\ManyToOne(targetEntity: League::class)]
  #[ORM\JoinColumn(name: "turnier", referencedColumnName: "id")]
  private League $league;

  #[ORM\Id]
  #[ORM\ManyToOne(targetEntity: Division::class)]
  #[ORM\JoinColumn(name: "staffel", referencedColumnName: "id")]
  private Division $division;

  #[ORM\Id]
  #[ORM\Column(name: 'runde')]
  private int $round;

  #[ORM\Id]
  #[ORM\Column(name: 'typ')]
  private string $type;


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
}
