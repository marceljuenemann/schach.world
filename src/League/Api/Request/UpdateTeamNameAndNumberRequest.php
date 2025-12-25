<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTeamNameAndNumberRequest
{
  #[Assert\NotBlank]
  #[Assert\Length(['min' => 3, 'max' => 20])]
  public string $name;

  #[Assert\GreaterThanOrEqual(1)]
  #[Assert\LessThan(100)]
  public int $number;
}
