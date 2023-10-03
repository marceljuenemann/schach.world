<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comments added to a specific round.
 */
#[ORM\Entity]
#[ORM\Table(name: 'bemerkungen')]
class RoundComment
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private int $id;

  #[ORM\ManyToOne(targetEntity: Division::class)]
  #[ORM\JoinColumn(name: "staffel", referencedColumnName: "id")]
  private Division $division;

  #[ORM\Column(name: 'runde')]
  private int $round;

  #[ORM\Column(name: 'text')]
  private string $text;

  #[ORM\Column(name: 'timestamp')]
  private string $lastModified;

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
