<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nsv\League\Core\Encoding;

#[ORM\Entity]
#[ORM\Table(name: 'mannschaften')]
class Team
{
  const DEFAULT_GROUP = 'default';

  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private int $id;

  #[ORM\ManyToOne(targetEntity: League::class, inversedBy: 'divisions')]
  #[ORM\JoinColumn(name: "turnier", referencedColumnName: "id")]
  private League $league;

  // TODO: Use ManyToOne once zero values have been replaces with null.
  #[ORM\Column(name: 'staffel')]
  private int $divisionId = 0;

  #[ORM\Column(length: 20)]
  private string $name;

  #[ORM\Column(name: 'mnr')]
  private int $number;

  /**
   * The ZPS code (German Federation ID) of the club. Usually five digits,
   * but if two clubs are partnering, their ZPS codes are concatenated.
   */
  #[ORM\Column(length: 10)]
  private ?string $zps;

  /**
   * If two teams are in different groups, they can never be substitutes for
   * each other. This is useful if a league has many divisions that aren't all
   * created equal, e.g. if they have different age groups.
   * 
   * TODO: Instead create a group column on the division entity. Also show that
   * group in the UI so that teams can more easily be distinguished.
   */
  #[ORM\Column(name: 'gruppe')]
  private string $group = self::DEFAULT_GROUP;

  #[ORM\Column(name: 'so_name', length: 40)]
  private ?string $venueName;

  #[ORM\Column(name: 'so_hinweis', length: 255)]
  private string $venueNote = '';

  #[ORM\Column(name: 'so_strasse', length: 30)]
  private ?string $venueStreet;

  #[ORM\Column(name: 'so_plz', length: 5)]
  private ?string $venuePostCode;

  #[ORM\Column(name: 'so_stadt', length: 30)]
  private ?string $venueCity;

  #[ORM\Column(name: 'so_telefon', length: 15)]
  private ?string $venuePhone;

  #[ORM\Column(name: 'mf_name', length: 40)]
  private ?string $captainName = '';

  #[ORM\Column(name: 'mf_email', length: 50)]
  private ?string $captainMail = '';

  #[ORM\Column(name: 'mf_telefon', length: 30)]
  private ?string $captainPhone = '';

  #[ORM\Column(name: 'mf_telefon2', length: 30)]
  private ?string $captainPhone2 = '';

  #[ORM\OneToMany(targetEntity: Player::class, mappedBy: 'team')]   
  #[ORM\OrderBy(["number" => "ASC"])]
  private $players;

  #[ORM\OneToMany(targetEntity: TeamRecipient::class, mappedBy: 'team')]   
  #[ORM\OrderBy(["id" => "ASC"])]
  private $additionalRecipients;

  #[ORM\OneToMany(targetEntity: TeamDetail::class, mappedBy: 'team')]   
  private $details;

  public function nameWithNumber() {
    return trim(trim($this->name) . ' ' . ($this->number > 1 ? $this->number : ''));
  }

  public function uri() {
    return $this->league->uri() . "m/" . $this->id . "/";
  }

  public function apiUri() {
    return $this->league->uri() . "api/teams/" . $this->id . "/";
  }

  /**
   * Whether the given team is a substitute team for this one.
   */
  public function isSubstituteTeam(Team $team) {
    if ($this->number >= $team->number) return false;
    if ($this->zps) {
      if ($this->zps != $team->zps) return false;
    } else {
      if ($this->name != $team->name) return false;
    }
    if ($this->group != $team->group) return false;
    return $team->number <= $this->number + $this->league->configSubstituteTeams;
  }

  /**
   * Yields all substitute teams for this team.
   */
  public function substituteTeams() {
    if (!$this->league->configSubstituteTeams) return;
    foreach ($this->league->teams as $team) {
      if ($this->isSubstituteTeam($team)) {
        yield $team;
      }
    }
  }

  public function detail(string $key): TeamDetail|null {
    foreach ($this->details as $detail) {
      if ($detail->key === $key) {
        return $detail;
      }
    }
    return null;
  }

  public function isVenueAccessible(): bool {
    $detail = $this->detail(Encoding::utf8_decode(TeamDetail::KEY_ACCESSIBLE));
    return $detail && $detail->isTrue();
  }

  public function hasAccessibleToilet(): bool {
    $detail = $this->detail(Encoding::utf8_decode(TeamDetail::KEY_ACCESSIBLE_TOILET));
    return $detail && $detail->isTrue();
  }

  public function __call($property, $args) {
    return $this->$property;
  }

  public function __get($property) {
    if ($property === 'division') {
      // TODO: Use ManyToOne once zero values have been replaces with null.
      return $this->divisionId ? $this->league->divisionById($this->divisionId) : null;
    }
    return $this->$property;
  }

  public function __set($property, $value) {
    if ($property === 'division') {
      // TODO: Use ManyToOne once zero values have been replaces with null.
      $this->divisionId = $value->id;
    }
    $this->$property = $value;
  }
}
