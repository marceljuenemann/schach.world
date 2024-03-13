<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * eMail recipient for a team who receives eMails in addition to the 
 * 
 * Note: The table also contains fields for configuring which type of
 * eMails to receive. However, it's a rarely used feature that adds
 * unnecessary complexity, so we now just send all eMails.
 * TODO: Delete additional database fields once fully migrated.
 */
#[ORM\Entity]
#[ORM\Table(name: 'zusatzempfaenger')]
class TeamRecipient
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private int $id;

  #[ORM\ManyToOne(targetEntity: Team::class, inversedBy: 'additionalRecipients')]
  #[ORM\JoinColumn(name: "mannschaft", referencedColumnName: "id")]
  private $team;

  #[ORM\Column(name: 'email')]
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
