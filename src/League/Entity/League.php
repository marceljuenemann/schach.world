<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A league runs for one season and consists of one or more divisions.
 */
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

    #[ORM\OneToMany(targetEntity: Date::class, mappedBy: 'league')]
    #[ORM\OrderBy(["division" => "DESC"])]
    private $dates;

    public function uri() {
      return "/ligen/{$this->path}/";
    }
    
    /**
     * @throws NotFoundHttpException
     */
    public function divisionByPath($path) {
      foreach ($this->divisions as $division) {
        if ($path === $division->path()) {
          return $division;
        }
      }
      throw new NotFoundHttpException('Division not found');
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
