<?php

namespace Nsv\Registration\Api\Model;

use Nsv\Dwz\Api\Model\PlayerData;
use Nsv\Registration\Api\Model\ContactDetails;
use Nsv\Registration\Entity as Entity;
use Symfony\Component\Validator\Constraints as Assert;

class PlayerRegistration
{
  public ?int $id = null;

  #[Assert\NotBlank]
  public string $group;

  public bool $waitlist = false;

  #[Assert\NotBlank]
  #[Assert\Valid]
  public PlayerData $playerData;

  #[Assert\All([new Assert\Type('string')])]
  public array $additionalFields;

  #[Assert\NotBlank]
  #[Assert\Valid]
  public ContactDetails $contactDetails;

  public ?string $created;

  static function fromEntity(Entity\PlayerRegistration $player, bool $includeSensitive): PlayerRegistration {
    $reg = new PlayerRegistration();
    $reg->id = $player->id;
    $reg->group = $player->group;
    $reg->waitlist = $player->waitlist;

    $reg->playerData = $p = new PlayerData();
    $p->name = $player->name;
    $p->zps = $player->dwzPlayer?->zps;
    $p->memberId = $player->dwzPlayer?->memberId;
    $p->gender = $player->gender;
    $p->fideTitle = $player->fideTitle;
    $p->fideId = $player->fideId;

    // Populate from DWZ database where possible.
    $p->club = $player->club ?? $player->dwzPlayer?->club->name ?? '';
    $p->dwz = $player->dwz ?? $player->dwzPlayer?->dwz;
    $p->elo = $player->elo ?? $player->dwzPlayer?->elo;

    // Only populate if authorized.
    if ($includeSensitive) {
      $p->yearOfBirth = $player->yearOfBirth;
      $reg->contactDetails = ContactDetails::fromEntity($player);
      $reg->created = $player->created->format('Y-m-d H:i');
    }
    return $reg;
  }
}
