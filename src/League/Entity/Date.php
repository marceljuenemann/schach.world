<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Date for a specific round. A Date can either be applicable for the entire league or only
 * a specific division. In the latter case it overrides the league-wide Date.
 */
#[ORM\Entity]
#[ORM\Table(name: 'termine')]
class Date
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: League::class, inversedBy: 'dates')]
    #[ORM\JoinColumn(name: "turnier", referencedColumnName: "id")]
    private League $league;

    #[ORM\ManyToOne(targetEntity: Division::class)]
    #[ORM\JoinColumn(name: "staffel", referencedColumnName: "id")]
    private ?Division $division;

    #[ORM\Column(name: 'runde')]
    private int $round;

    #[ORM\Column(name: 'datum')]
    private string $date;

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
