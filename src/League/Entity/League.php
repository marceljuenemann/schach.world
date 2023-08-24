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

  /**
   * For German chess federations, this should be the ZPS code prefix of the organisation.
   * 
   * Note that some organisation-specific rules are hardcoded in Regulation.php
   */
  #[ORM\Column(name: 'organisation', length: 15)]
  private string $organisation;

  /**
   * The year in which the league started.
   */
  #[ORM\Column(name: 'startjahr')]
  private ?int $year;

  /**
   * The legacy user of the league manager.
   * 
   * TODO: Use WordPress user system.
   * TODO: Allow multiple users to manage a league.
   */
  #[ORM\OneToOne(targetEntity: LegacyUser::class)]
  #[ORM\JoinColumn(name: 'leiter', referencedColumnName: 'id')]
  private $manager;

  /**
   * Number of subsequent teams whose players may be used as substitute players.
   */
  #[ORM\Column(name: 'spielErsatzmannschaft')]
  private int $configSubstituteTeams = 0;

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
  public function divisionByPath(string $path) {
    foreach ($this->divisions as $division) {
      if ($path === $division->path()) {
        return $division;
      }
    }
    throw new NotFoundHttpException('Division not found');
  }

  /**
   * @throws NotFoundHttpException
   */
  public function divisionById(int $id): Division {
    foreach ($this->divisions as $division) {
      if ($id === $division->id) {
        return $division;
      }
    }
    throw new NotFoundHttpException('Division not found');
  }

  /**
   * @throws NotFoundHttpException
   */
  public function teamById($id) {
    // We always fetch all teams anyways, so this is the most efficient way.
    foreach ($this->teams as $team) {
      if ($team->id === $id) {
        return $team;
      }
    }
    throw new NotFoundHttpException('Team not found');
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
