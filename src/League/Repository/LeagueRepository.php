<?php

namespace Nsv\League\Repository;

use Nsv\League\Entity\League;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Team;

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

  /**
   * Returns the league with the given path, or if there is no exact match,
   * the most recent league that starts with the path.
   */
  public function findByPathOrPrefix(string $path): League|null {
    $league = $this->findOneByPath($path);
    if ($league) return $league;

    // Find the latest league that has at least one team.
    return $this->createQueryBuilder('l')
      ->where('l.path LIKE :path')
      ->andWhere('EXISTS (SELECT 1 FROM '.Team::class.' t WHERE t.league = l.id)')
      ->addOrderBy('l.year', 'DESC')
      ->setMaxResults(1)
      ->setParameter('path', $path.'-____')
      ->getQuery()
      ->getOneOrNullResult();
  }

  // TODO: Delete
  public function persist(League $entity) {
    $this->getEntityManager()->persist($entity);
  }

  public function getEntityManager(): EntityManagerInterface {
    return parent::getEntityManager();
  }
}
