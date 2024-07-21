<?php

namespace Nsv\Registration\Api\Request;

use Nsv\Dwz\Api\Model\PlayerData;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterPlayerRequest
{
  #[Assert\NotBlank]
  #[Assert\Valid]
  public PlayerData $playerData;
}
