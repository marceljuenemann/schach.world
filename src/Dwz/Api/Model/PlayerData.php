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
  
  public ?string $zps;

  public ?string $memberId;

  public string $status;

  public ?string $gender;
  
  public ?int $yearOfBirth;

  public ?int $dwz;

  public ?int $elo;

  /**
   * FIDE title, one of GM, IM, FM, CM, WG, WI, WF, WC, null.
   */
  public ?string $fideTitle;

  public ?int $fideId;

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
