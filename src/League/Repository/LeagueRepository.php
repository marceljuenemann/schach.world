<?php

namespace Nsv\League\Repository;

use Nsv\League\Entity\League;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<League>
 *
 * @method League|null find($id)
 */
class LeagueRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, League::class);
  }

  public function findByPath(string $path) {
    return $this->findOneBy(array('path' => $path));
  }
}
