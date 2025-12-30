<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTeamCaptainRequest
{
  public ?string $name;

  #[Assert\Email]
  public ?string $mail;

  public ?string $phone;

  public ?string $phone2;
}
