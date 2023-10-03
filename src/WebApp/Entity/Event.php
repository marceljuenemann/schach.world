<?php

namespace Nsv\WebApp\Entity;

use Nsv\WebApp\Repository\EventRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: 'termine2')]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 100)]
    #[Assert\Length(min: 3, max: 100)]
    private string $name;

    #[ORM\Column(length: 200)]
    #[Assert\Length(min: 10, max: 200)]
    private string $url;

    #[ORM\Column(type: 'date')]
    private \DateTimeInterface $date;

    #[ORM\Column('is_nsv')]
    private bool $isNsv = false;

    #[ORM\Column]
    private bool $isApproved = false;

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
