<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Division;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
      throw new NotFoundHttpException("Division not found");
    }
    return $entity;
  }

  // TODO: Delete
  public function persist(Division $entity) {
    $this->getEntityManager()->persist($entity);
  }

  /**
   * We have quite some divisions where the league
   * is set to 0. Those are invalid and we only
   * want valid ones.
   */
  public function findAllThatHaveLeague(){
    return $this->createQueryBuilder('divisions')
      ->select('divisions')
      ->innerJoin('divisions.league', 'league')
      ->getQuery()
      ->getResult();
  }
}
