<?php

namespace Nsv\WebApp\Entity;

use Nsv\WebApp\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'termine2')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 100)]
    private string $name;

    #[ORM\Column]
    private string $date;

    #[ORM\Column]
    private bool $isApproved;

    public function __call($property, $args) {
      return $this->$property;
    }

    public function __set($property, $value) {
      $this->$property = $value;
    }
}
