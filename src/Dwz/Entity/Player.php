<?php

namespace Nsv\Dwz\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dwz_spieler')]
class Player
{
  /**
   * Player name in format "Last name,First name[,Title]".
   */
  #[ORM\Id]
  #[ORM\Column(name: 'Spielername', length: 40)]
  private string $name;

  public function __get($property) {
    return $this->$property;
  }

  public function __set($property, $value) {
    $this->$property = $value;
  }
}
