<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Legacy user entity.
 * 
 * TODO: Use WordPress user system.
 */
#[ORM\Entity]
#[ORM\Table(name: 'benutzer')]
class LegacyUser
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private int $id;

  #[ORM\Column(name: 'name', length: 60)]
  private string $name;

  /**
   * Legacy password field. Hashed with md5 without salt.
   */
  #[ORM\Column(name: 'passwort', length: 35)]
  private string $password;

  #[ORM\Column(name: 'telefon', length: 30)]
  private ?string $phone;

  #[ORM\Column(name: 'telefon2', length: 30)]
  private ?string $phone2;

  #[ORM\Column(name: 'email', length: 50)]
  private string $mail;

  public function __call($property, $args) {
    return $this->$property;
  }

  public function __get($property) {
    return $this->$property;
  }

  public function __set($property, $value) {
    $this->$property = $value;
  }
}
