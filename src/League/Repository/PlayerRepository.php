<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Player;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Player>
 *
 * @method Player|null find($id)
 */
class PlayerRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Player::class);
  }

  // TODO: move into our own abstract repository? 
  public function find($id, $lockMode = null, $lockVersion = null): Player {
    $entity = parent::find((int) $id, $lockMode, $lockVersion);
    if (!$entity) {
      throw new NotFoundHttpException("Player not found");
    }
    return $entity;
  }

  /**
   * Returns all players who were late registered.
   */
  public function findLateRegistrations(Division $division, int $minRound, int $maxRound) {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->select('p')
      ->from(Player::class, 'p')
      ->where('p.lateRegistrationDivision = :division')
      ->andWhere('p.lateRegistrationRound >= :minRound')
      ->andWhere('p.lateRegistrationRound <= :maxRound')
      ->addOrderBy('p.team', 'ASC')
      ->addOrderBy('p.number', 'ASC')
      ->setParameter('division', $division)
      ->setParameter('minRound', $minRound)
      ->setParameter('maxRound', $maxRound)
      ->getQuery()
      ->getResult();
  }
}
