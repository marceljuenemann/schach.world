<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class CreateDivisionRequest
{
  #[Assert\NotBlank]
  #[Assert\Length(['min' => 2])]
  public string $name;

  #[Assert\NotBlank]
  #[Assert\Length(['min' => 5])]
  public string $managerName;

  #[Assert\NotBlank]
  #[Assert\Email]
  public string $managerMail;

  public ?string $managerPhone = null;

  public ?string $managerPhone2 = null;

  #[Assert\NotBlank]
  #[Assert\Length(['min' => 8])]
  public string $managerPassword;
}
