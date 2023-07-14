<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nsv\League\Repository\LeagueRepository;

#[ORM\Entity(repositoryClass: LeagueRepository::class)]
#[ORM\Table(name: 'turniere')]
class League
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 40)]
    private string $name;

    #[ORM\Column(name: 'directory', length: 20)]
    private string $path;

    #[ORM\OneToMany(targetEntity: Division::class, mappedBy: 'league')]   
    #[ORM\OrderBy(["sortId" => "ASC", "id" => "ASC"])]
    private $divisions;

    #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'league')]
    #[ORM\OrderBy(["name" => "ASC", "number" => "ASC"])]
    private $teams;

    public function divisionByPath($path) {
      foreach ($this->divisions as $division) {
        if ($path === $division->path()) {
          return $division;
        }
      }
      // TODO: Guess we'd want a better exception here that we can actually catch.
      throw new \Exception('Invalid division path');
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
