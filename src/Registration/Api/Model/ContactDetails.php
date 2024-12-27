<?php

namespace Nsv\Registration\Api\Model;

use Nsv\Registration\Entity as Entity;
use Symfony\Component\Validator\Constraints as Assert;

class ContactDetails
{
  #[Assert\NotBlank]
  #[Assert\Length(min: 5)]
  public string $name;

  #[Assert\NotBlank]
  #[Assert\Email]
  public string $email;  

  static function fromEntity(Entity\PlayerRegistration $reg): ContactDetails {
    $contact = new ContactDetails();
    $contact->name = $reg->contactName;
    $contact->email = $reg->contactEMail;
    return $contact;
  }
}
