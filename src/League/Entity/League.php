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
