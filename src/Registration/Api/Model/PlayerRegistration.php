<?php

namespace Nsv\Registration\Api\Model;

use Nsv\Dwz\Api\Model\PlayerData;
use Nsv\Registration\Api\Model\ContactDetails;
use Nsv\Registration\Entity as Entity;
use Symfony\Component\Validator\Constraints as Assert;

class PlayerRegistration
{
  public ?int $id;

  #[Assert\NotBlank]
  public string $group;

  #[Assert\NotBlank]
  #[Assert\Valid]
  public PlayerData $playerData;

  #[Assert\NotBlank]
  #[Assert\Valid]
  public ContactDetails $contactDetails;


  static function fromEntity(Entity\PlayerRegistration $player): PlayerRegistration {
    $reg = new PlayerRegistration();
    $reg->id = $player->id;
    $reg->group = $player->group;

    $reg->playerData = $p = new PlayerData();
    $p->name = $player->name;
    $p->zps = $player->zps;
    $p->memberId = $player->memberId;
    $p->gender = $player->gender;
    $p->fideTitle = $player->fideTitle;
    $p->fideId = $player->fideId;
    $p->fideCountry = $player->fideCountry;

    // Populate from DWZ database where possible.
    $p->club = $player->club ?? $player->dwzPlayer?->club->name ?? '';
    $p->dwz = $player->dwz ?? $player->dwzPlayer?->dwz;
    $p->elo = $player->elo ?? $player->dwzPlayer?->elo;

    // Only populate if authorized.
    // $p->yearOfBirth = $player->yearOfBirth;
    //$reg->contactDetails = ContactDetails::fromEntity($player);

    return $reg;
  } 
}
