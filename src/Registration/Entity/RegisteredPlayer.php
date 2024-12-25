<?php

namespace Nsv\Registration\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'registration_players')]
class RegisteredPlayer
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  public int $id;

  #[ORM\Column(length: 20)]
  public string $tournament;

  #[ORM\Column(name: "tournament_group", length: 20)]
  public string $group;

  #[ORM\Column(length: 60)]
  public string $name;

  /**
   * If null, the club name should be populated from the DWZ database where possible.
   */
  #[ORM\Column(length: 60)]
  public ?string $club;
  
  #[ORM\Column(length: 5)]
  public ?string $zps;

  #[ORM\Column(name: "member_id", length: 4)]
  public ?string $memberId;

  #[ORM\Column(length: 1)]
  public ?string $gender;
  
  #[ORM\Column(name: "year_of_birth")]
  public ?int $yearOfBirth;

  /**
   * If null, the DWZ should be populated from the DWZ database where possible.
   */
  #[ORM\Column(name: "dwz")]
  public ?int $dwz;

  /**
   * If null, the ELO should be populated from the DWZ database where possible.
   */
  #[ORM\Column(name: "elo")]
  public ?int $elo;

  #[ORM\Column(name: "fide_title", length: 3)]
  public ?string $fideTitle;

  #[ORM\Column(name: "fide_id")]
  public ?int $fideId;

  #[ORM\Column(name: "fide_country", length: 3)]
  public ?string $fideCountry;

  #[ORM\Column(name: "contact_name", length: 60)]
  public string $contactName;

  #[ORM\Column(name: "contact_email", length: 100)]
  public string $contactEMail;
}
