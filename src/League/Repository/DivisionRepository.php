<?php

namespace Nsv\League\Repository;

use Nsv\League\Entity\League;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Division;

/**
 * @extends ServiceEntityRepository<League>
 *
 * @method Division|null find($id)
 */
class DivisionRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Division::class);
  }
}
