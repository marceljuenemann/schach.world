<?php

namespace Nsv\Registration\Api\Request;

use Nsv\Dwz\Api\Model\PlayerData;
use Nsv\Registration\Api\Model\ContactDetails;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterPlayerRequest
{
  #[Assert\NotBlank]
  #[Assert\Valid]
  public PlayerData $playerData;

  #[Assert\NotBlank]
  #[Assert\Valid]
  public ContactDetails $contactDetails;
}
