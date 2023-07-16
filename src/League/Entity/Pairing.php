<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A pairing is a match between two teams. It belongs to a specific division and round and
 * will store the result.
 * 
 * NB: This class would be called Match if that wasn't a reserved keyword in PHP.
 */
#[ORM\Entity]
#[ORM\Table(name: 'paarungen')]
class Pairing
{
    // A magic date we set as $customDate if the game is moved, but no date has been set. 
    const UNKNOWN_DATE = '2020-12-24';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Division::class, inversedBy: 'pairings')]
    #[ORM\JoinColumn(name: "staffel", referencedColumnName: "id")]
    private Division $division;

    #[ORM\Column(name: 'runde')]
    private int $round;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: "mannschaft1", referencedColumnName: "id")]
    private Team $team1;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: "mannschaft2", referencedColumnName: "id")]
    private Team $team2;

    #[ORM\Column(name: 'erg1')]
    private ?float $result1;

    #[ORM\Column(name: 'erg2')]
    private ?float $result2;

    #[ORM\Column(name: 'bemerkung', length: 200)]
    private ?string $comment;

    /**
     * If the pairing is hosted by someone other than team1, this field is set.
     */
    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(name: "ausrichter", referencedColumnName: "id")]
    private ?Team $host;

    /**
     * If this pairing was moved to a different date, this field is set.
     */
    #[ORM\Column(name: 'termin')]
    private ?string $customDate;

    /**
     * Whether the eMail with the link for entering the game was already sent.
     */
    #[ORM\Column(name: 'linkGesendet')]
    private bool $linkSent;

    /**
     * Whether the pairing is locked for changes by teams.
     */
    #[ORM\Column(name: 'festgelegt')]
    private bool $locked;

    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: 'pairing')]   
    #[ORM\OrderBy(["board" => "ASC"])]
    private $games;

    public function wasMoved(): bool {
      return (bool) $this->customDate;
    }

    public function moveDate(): string|null {
      return $this->wasMoved() && $this->customDate != self::UNKNOWN_DATE ? $this->customDate : null;
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
