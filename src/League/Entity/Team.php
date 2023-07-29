<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nsv\WebApp\Core\Linkable;

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

    #[ORM\Column(name: 'so_name', length: 40)]
    private ?string $venueName;

    #[ORM\Column(name: 'so_hinweis', length: 255)]
    private ?string $venueNote;

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

    public function nameWithNumber() {
      return trim(trim($this->name) . ' ' . ($this->number > 1 ? $this->number : ''));
    }

    public function uri() {
      return $this->league->uri() . "?mannschaft=" . $this->id;
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
