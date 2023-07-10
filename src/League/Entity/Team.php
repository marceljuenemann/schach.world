<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'mannschaften')]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: League::class, inversedBy: 'divisions')]
    #[ORM\JoinColumn(name: "turnier", referencedColumnName: "id")]
    private $league;

    #[ORM\ManyToOne(targetEntity: Division::class, inversedBy: 'teams')]
    #[ORM\JoinColumn(name: "staffel", referencedColumnName: "id")]
    private $division;

    #[ORM\Column(length: 20)]
    private string $name;

    #[ORM\Column(name: 'mnr')]
    private int $number;

    public function nameWithNumber() {
      return trim($this->name . ' ' . ($this->number > 1 ? $this->number : ''));
    }

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
