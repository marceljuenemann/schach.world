<?php

namespace Nsv\League\Api\Model;

use Nsv\League\Entity;

class Player
{
  public int $id;
  public string $name;
  public string $lastName;
  public string $firstName;
  public string $title;
  public Team $team;
  public int $number;
  public ?int $dwz;
  public ?int $elo;
  public string $gender;
  public string $uri;

  public static function fromEntity(Entity\Player $player) {
    $result = new Player();
    $result->id = $player->id;
    $result->lastName = $player->lastName;
    $result->firstName = $player->firstName;
    $result->title = $player->title;
    $result->team = Team::fromEntity($player->team);
    $result->number = $player->number;
    $result->dwz = $player->dwz;
    $result->elo = $player->elo;
    $result->gender = $player->gender;
    $result->uri = $player->uri();
    return $result;
  }
}
