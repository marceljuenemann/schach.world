<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Team;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Nsv\League\Entity\Division;

/**
 * @extends ServiceEntityRepository<Team>
 */
class TeamRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Team::class);
  }

  // TODO: move into our own abstract repository? 
  public function find($id, $lockMode = null, $lockVersion = null): Team {
    $entity = parent::find((int)$id, $lockMode, $lockVersion);
    if (!$entity) {
      throw new NotFoundHttpException("Team not found");
    }
    return $entity;
  }

  /**
   * Find all teams in a division
   */
  // TODO: Replace with $division->teams.
  public function findByDivision(Division $division) {
    return $this->createQueryBuilder('team')
      ->select('team', 'players')
      ->leftJoin('team.players', 'players')
      ->andWhere('team.divisionId = :division')
      ->setParameter('division', $division->id)
      ->getQuery()
      ->getResult();
  }

}
