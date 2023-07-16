<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'spieler')]
class Player
{
  // Note: Unfortunately, the DSB database only contains male or female :(
  const GENDER_MALE = 'm';
  const GENDER_FEMALE = 'w';

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private int $id;

  #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'players')]
  #[ORM\JoinColumn(name: "mannschaft", referencedColumnName: "id")]
  private $team;

  /**
   * Number within the team. Usually these numbers are sequential from 1
   * to number of players in the team. However, we also support numbers prefixed
   * with the team number, e.g. 203 for the third player in the second team.
   */
  #[ORM\Column(name: 'brettnr')]
  private int $number;

  #[ORM\Column(name: 'vorname', length: 20)]
  private string $firstName;

  #[ORM\Column(name: 'nachname', length: 20)]
  private string $lastName;

  /**
   * The title field is used both for FIDE titles (GM, WFM etc.) and academic titles
   * like Prof. or Dr.
   */
  #[ORM\Column(name: 'titel', length: 15)]
  private string $title;

  #[ORM\Column]
  private ?int $dwz;

  #[ORM\Column]
  private ?int $elo;

  /**
   * Usually contains just the birth year, but can also contain the full birth date,
   * especially for players who weren't found in the FIDE or DWZ database. Note there
   * is currently no enforcement of the format whatsoever.
   */
  #[ORM\Column(name: 'geburt', length: 13)]
  private string $birth;

  /**
   * Player's gender as single character, should be one of the constants defined above.
   */
  #[ORM\Column(name: 'geschlecht')]
  private string $gender;

  /**
   * If the player was registered late, the division for which they were registered.
   */
  #[ORM\ManyToOne(targetEntity: Division::class)]
  #[ORM\JoinColumn(name: "nmSid", referencedColumnName: "id")]
  private ?Division $lateRegistrationDivision;    

  /**
   * If the player was registered late, the round to which they were registered.
   */
  private ?int $lateRegistrationRound;

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
