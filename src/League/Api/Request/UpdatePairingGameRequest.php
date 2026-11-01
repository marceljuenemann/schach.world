<?php

namespace Nsv\League\Api\Request;

use Nsv\League\Core\Result;
use Symfony\Component\Validator\Constraints as Assert;

class UpdatePairingGameRequest
{
  #[Assert\NotNull]
  public int $board;

  public ?int $player1 = null;

  #[Assert\Valid]
  public ?LateRegistrationRequest $lateRegistration1 = null;

  public ?int $player2 = null;

  #[Assert\Valid]
  public ?LateRegistrationRequest $lateRegistration2 = null;

  #[Assert\NotNull]
  #[Assert\Choice(choices: Result::UNICODE_RESULTS)]
  public string $result1;

  #[Assert\NotNull]
  #[Assert\Choice(choices: Result::UNICODE_RESULTS)]
  public string $result2;
}
