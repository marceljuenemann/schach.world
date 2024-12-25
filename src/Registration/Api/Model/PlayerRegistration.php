<?php

namespace Nsv\Registration\Api\Model;

use Nsv\Dwz\Api\Model\PlayerData;
use Nsv\Registration\Api\Model\ContactDetails;
use Nsv\Registration\Entity\RegisteredPlayer;
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


  static function fromEntity(RegisteredPlayer $player): PlayerRegistration {
    $reg = new PlayerRegistration();
    $reg->id = $player->id;
    $reg->group = $player->group;
    //$reg->playerData = PlayerData::fromEntity($player);
    //$reg->contactDetails = ContactDetails::fromEntity($player);
    return $reg;
  } 
}
