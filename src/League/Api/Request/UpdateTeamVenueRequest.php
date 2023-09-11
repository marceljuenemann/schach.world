<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTeamVenueRequest
{
  public ?string $name;

  public ?string $note;

  public ?string $street;

  #[Assert\Regex('/^\d{5}$/')]
  public ?string $postCode;

  public ?string $city;

  public ?string $phone;
}
