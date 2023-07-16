<?php

namespace Nsv\League\Api\Model;

use Nsv\Dwz\DsbDatabase;
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
  public ?string $zps;
  public ?int $dwz;
  public ?int $elo;
  public string $gender;
  public string $uri;
  public ?string $dsbUri;

  public ?array $games;

  public function addGame(Entity\Game $game) {
    if (!isset($this->games)) $this->games = array();
    $this->games[] = PlayerGame::forPlayer($this, $game);
  }

  public static function fromEntity(Entity\Player $player) {
    $result = new Player();
    $result->id = $player->id;
    $result->name = $player->name();
    $result->lastName = $player->lastName;
    $result->firstName = $player->firstName;
    $result->title = $player->title();
    $result->team = Team::fromEntity($player->team);
    $result->number = $player->number;
    $result->zps = $player->zps ?: null;
    $result->dwz = $player->dwz ?: null;
    $result->elo = $player->elo ?: null;
    $result->gender = $player->gender;
    $result->uri = $player->uri();
    $result->dsbUri = $result->zps ? DsbDatabase::playerRecordUri($result->zps) : null;
    return $result;
  }
}
