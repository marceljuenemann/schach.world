<?php

namespace Nsv\League\Api\Model;

use Nsv\Dwz\DsbDatabase;
use Nsv\League\Core\Result;
use Nsv\League\Entity;

class Player
{
  public int $id;
  public string $name;
  public string $lastName;
  public string $firstName;
  public string $title;
  public int $number;
  public ?string $zps;
  public ?int $dwz;
  public ?int $elo;
  public string $gender;
  public bool $isGuest;
  public ?int $lateRegistrationRound;
  public string $uri;
  public ?string $dsbUri;

  public ?Team $team;
  public ?array $games;
  public ?int $gameCount;
  public ?float $points;
  public array|null $dwzCalculation;

  public function addGame(Entity\Game $game) {
    if (!isset($this->games)) $this->games = array();
    $this->games[] = PlayerGame::forPlayer($this->id, $game);
  }

  public function pointsFormatted() {
    return Result::format($this->points);
  }

  public static function fromEntity(Entity\Player|null $player): Player|null {
    if (!$player) return null;
    $result = new Player();
    $result->id = $player->id;
    $result->name = $player->name();
    $result->lastName = $player->lastName;
    $result->firstName = $player->firstName;
    $result->title = $player->title();
    $result->number = $player->number;
    $result->zps = $player->zps ?: null;
    $result->dwz = $player->dwz ?: null;
    $result->elo = $player->elo ?: null;
    $result->gender = $player->gender;
    $result->isGuest = $player->isGuest();
    $result->lateRegistrationRound = $player->lateRegistrationRound ?: null;
    $result->uri = $player->uri();
    $result->dsbUri = $result->zps ? DsbDatabase::playerRecordUri($result->zps) : null;
    return $result;
  }
}
