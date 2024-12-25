<?php

namespace Nsv\Dwz\Api\Model;

use Nsv\Dwz\DsbDatabase;
use Nsv\Dwz\Entity\Player;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Data about a chess player. The format is very close to the
 * DWZ Player entity provided by the DSB, but with some small
 * improvements. This object is designed to also be used by
 * other components of the system for transfering player data.
 */
class PlayerData
{
  #[Assert\NotBlank]
  #[Assert\Regex('/^.+, .+$/')]
  public string $name;

  public string $club;
  
  #[Assert\Regex('/^[A-Z0-9]{5}$/')]
  public ?string $zps;

  #[Assert\Regex('/^[0-9]{1,4}$/')]
  public ?string $memberId;

  #[Assert\Regex('/^A|P$/')]
  public ?string $status;

  #[Assert\Regex('/^[MWD]?$/')]
  public ?string $gender;
  
  #[Assert\Range(min: 1900, max: 2100)]
  public ?int $yearOfBirth;

  #[Assert\Range(min: 100, max: 5000)]
  public ?int $dwz;

  #[Assert\Range(min: 100, max: 5000)]
  public ?int $elo;

  #[Assert\Regex('/^W?[GIFC]M?$/')]
  public ?string $fideTitle;

  public ?int $fideId;

  #[Assert\Regex('/^[A-Z]{3}$/')]
  public ?string $fideCountry;

  public ?string $uri;
  
  static function fromDwzEntity(Player $player): PLayerData {
    $data = new PlayerData();
    $data->name = str_replace(',', ', ', $player->name);
    $data->club = $player->club->name;
    $data->zps = $player->club->zps;
    $data->memberId = $player->memberId;
    $data->status = $player->status;
    $data->gender = $player->gender ?: 'M';
    $data->yearOfBirth = $player->yearOfBirth;
    $data->dwz = $player->dwz;
    $data->elo = $player->elo;
    $data->fideTitle = $player->fideTitle;
    if ($data->fideTitle && $data->fideTitle[0] == 'W') {
      $data->fideTitle += 'M';
    }
    $data->fideId = $player->fideId;
    $data->fideCountry = $player->fideCountry;
    $data->uri = DsbDatabase::playerRecordUri($player->fullZps());
    return $data;
  }
}
