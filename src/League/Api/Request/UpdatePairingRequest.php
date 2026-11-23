<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdatePairingRequest
{
  #[Assert\NotNull]
  #[Assert\Range(min: 0)]
  #[Assert\DivisibleBy(0.5)]
  public float $result1;

  #[Assert\NotNull]
  #[Assert\Range(min: 0)]
  #[Assert\DivisibleBy(0.5)]
  public float $result2;

  #[Assert\Length(max: 200)]
  public ?string $comment = null;

  #[Assert\NotNull]
  public bool $sendConfirmation;

  /**
   * @var UpdatePairingGameRequest[]
   */
  #[Assert\NotNull]
  #[Assert\Valid]
  public array $games;
}
