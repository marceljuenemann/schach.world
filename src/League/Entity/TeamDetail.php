<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Detail information for a team as entered during registration process.
 */
#[ORM\Entity]
#[ORM\Table(name: 'anmeldungZusatzfelder')]
class TeamDetail
{
  // TODO: Turn these into proper first class fields on the Team entity.
  const KEY_ACCESSIBLE = 'Verfügt das Spiellokal über barrierefreien Zugang?';
  const KEY_ACCESSIBLE_TOILET = 'Verfügt das Spiellokal über eine Behindertentoilette?';

  const VALUE_TRUE = 'ja';

  #[ORM\Id]
  #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'details')]
  #[ORM\JoinColumn(name: "mannschaft", referencedColumnName: "id")]
  private $team;

  #[ORM\Id]
  #[ORM\Column(name: 'feldname', length: 60)]
  private string $key;

  #[ORM\Column(name: 'inhalt')]
  private string $value;

  public function isTrue(): bool {
    return strtolower($this->value) == self::VALUE_TRUE; 
  }

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
