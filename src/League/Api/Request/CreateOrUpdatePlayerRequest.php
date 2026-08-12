<?php

namespace Nsv\League\Api\Request;

use Nsv\League\Entity;
use Symfony\Component\Validator\Constraints as Assert;

class CreateOrUpdatePlayerRequest
{
  public ?string $firstName = null;

  #[Assert\NotBlank]
  #[Assert\Length(min: 2, max: 20)]
  public string $lastName;

  #[Assert\Length(max: 15)]
  public ?string $title = null;

  #[Assert\Regex('/^$|^[0-9A-Z]{5}-\d{1,4}$/')]
  public ?string $zps = null;

  #[Assert\Range(min: 0, max: 9999)]
  public ?int $dwz = null;

  #[Assert\Range(min: 0, max: 9999)]
  public ?int $elo = null;

  #[Assert\Range(min: 1900, max: 2100)]
  public ?int $yearOfBirth = null;

  #[Assert\Choice(choices: ['', Entity\Player::GENDER_MALE, Entity\Player::GENDER_FEMALE, Entity\Player::GENDER_DIVERSE])]
  public ?string $gender = null;

  #[Assert\Positive]
  public ?int $lateRegistrationRound = null;
}
