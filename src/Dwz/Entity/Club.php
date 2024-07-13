<?php

namespace Nsv\Dwz\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dwz_vereine')]
class Club
{
  #[ORM\Id, ORM\Column('ZPS', length: 5)]
  public string $zps;

  #[ORM\Column(name: 'Vereinname', length: 40)]
  public string $name;
}
