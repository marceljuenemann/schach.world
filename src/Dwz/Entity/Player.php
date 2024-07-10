<?php

namespace Nsv\Dwz\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dwz_spieler')]
class Player
{
  // Players can be in multiple clubs, but only actively in one.
  const STATUS_ACTIVE = 'A';
  const STATUS_PASSIVE = 'P';

  // Note: Unfortunately, the DSB database only contains null for male or W for female :(
  const GENDER_FEMALE = 'W';

  /*
  #[ORM\ManyToOne(targetEntity: Club::class)]
  #[ORM\JoinColumn(name: "ZPS", referencedColumnName: "ZPS")]
  public $club;
  */
  
  #[ORM\Id, ORM\Column('ZPS', length: 5)]
  public string $zps;

  #[ORM\Id, ORM\Column('Mgl_Nr', length: 4)]
  public string $memberId;

  #[ORM\Column('Status', length: 1)]
  public string $status;

  /**
   * Player name in format "Last name,First name[,Title]".
   */
  #[ORM\Column(name: 'Spielername', length: 40)]
  public string $name;

  #[ORM\Column(name: 'Geschlecht', length: 1)]
  public ?string $gender;
  
  #[ORM\Column(name: 'Geburtsjahr')]
  public int $yearOfBirth;

  #[ORM\Column(name: 'DWZ')]
  public ?int $dwz;

  #[ORM\Column(name: 'FIDE_Elo')]
  public ?int $elo;

  /**
   * FIDE title, one of GM, IM, FM, CM, WG, WI, WF, WC, null.
   */
  #[ORM\Column(name: 'FIDE_Titel', length: 2)]
  public ?string $fideTitle;

  #[ORM\Column(name: 'FIDE_ID')]
  public ?int $fideId;

  #[ORM\Column(name: 'FIDE_Land', length: 3)]
  public ?string $fideCountry;
}
