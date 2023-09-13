<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nsv\League\Core\Result;

/**
 * A game happens between two players and is part of a pairing.
 */
#[ORM\Entity]
#[ORM\Table(name: 'spielerpaarungen')]
class Game
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Pairing::class, inversedBy: 'games')]
    #[ORM\JoinColumn(name: "paarung", referencedColumnName: "id")]
    private Pairing $pairing;

    #[ORM\Column(name: 'brett')]
    private int $board;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: "spieler1", referencedColumnName: "id")]
    private ?Player $player1 = null;

    #[ORM\ManyToOne(targetEntity: Player::class)]
    #[ORM\JoinColumn(name: "spieler2", referencedColumnName: "id")]
    private ?Player $player2 = null;

    #[ORM\Column(name: 'ergebnis1')]
    private string $result1 = Result::UNKNOWN;

    #[ORM\Column(name: 'ergebnis2')]
    private string $result2 = Result::UNKNOWN;

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
