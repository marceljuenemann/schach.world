<?php

namespace Nsv\Registration\Api\Model;

use Symfony\Component\Validator\Constraints as Assert;

class ContactDetails
{
  #[Assert\NotBlank]
  #[Assert\Length(min: 5)]
  public string $name;

  #[Assert\NotBlank]
  #[Assert\Email]
  public string $email;  
}
