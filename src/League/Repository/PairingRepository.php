<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Pairing;

/**
 * @extends ServiceEntityRepository<Pairing>
 */
class PairingRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Pairing::class);
  }

  /**
   * Returns all pairings for the specified Round objects.
   */
  public function findByRounds(array $rounds) {
    $expr = Criteria::expr();
    $criteria = new Criteria();
    foreach ($rounds as $round) {
      $criteria->orWhere($expr->andX(
        $expr->eq('division', $round->division),
        $expr->eq('round', $round->round)
      ));
    }
    return $this->matching($criteria);
  }
}
