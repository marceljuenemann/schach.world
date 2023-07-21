<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Division;

/**
 * @extends ServiceEntityRepository<Division>
 */
class DivisionRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Division::class);
  }

  public function find($id, $lockMode = null, $lockVersion = null): Division {
    $entity = parent::find((int) $id, $lockMode, $lockVersion);
    if (!$entity) {
      throw new EntityNotFoundException("Division not found");
    }
    return $entity;
  }
}
