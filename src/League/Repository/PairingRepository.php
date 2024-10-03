<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Team;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Pairing>
 */
class PairingRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Pairing::class);
  }

  // TODO: move into our own abstract repository? 
  public function find($id, $lockMode = null, $lockVersion = null): Pairing {
    $entity = parent::find((int)$id, $lockMode, $lockVersion);
    if (!$entity) {
      throw new NotFoundHttpException("Pairing not found");
    }
    return $entity;
  }

  /**
   * Returns all pairings for the specified team, also fetching all games and players.
   */
  public function findByTeam(Team $team) {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->select('p, g, s1, s2')
      ->from(Pairing::class, 'p')
      ->leftJoin('p.games', 'g')
      // leftJoin to allow NULL players.
      ->leftJoin('g.player1', 's1')
      ->leftJoin('g.player2', 's2')
      ->where('(p.team1 = :team OR p.team2 = :team) AND p.division > 0')
      ->addOrderBy('p.round', 'ASC')
      ->addOrderBy('p.host', 'ASC')
      ->addOrderBy('p.id', 'ASC')
      ->setParameter('team', $team)
      ->getQuery()
      ->getResult();
  }

  /**
   * Returns all pairings for the specified round, also fetching all games and players.
   */
  public function findByRound(Division $division, int $round) {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->select('p, g, s1, s2')
      ->from(Pairing::class, 'p')
      ->leftJoin('p.games', 'g')
      // leftJoin to allow NULL players.
      ->leftJoin('g.player1', 's1')
      ->leftJoin('g.player2', 's2')
      ->where('p.division = :division AND p.division > 0 AND p.round = :round')
      ->addOrderBy('p.host', 'ASC')
      ->addOrderBy('p.id', 'ASC')
      ->setParameter('division', $division)
      ->setParameter('round', $round)
      ->getQuery()
      ->getResult();
  }

  /**
   * Returns all pairings for division, also fetching the teams.
   */
  public function findByDivisionWithTeams(Division $division) {
    return $this->createQueryBuilder('p')
      ->select('p, g, t1, t2')
      ->leftJoin('p.games', 'g')
      ->innerJoin('p.team1', 't1')
      ->innerJoin('p.team2', 't2')
      ->where('p.division = :division')
      ->addOrderBy('p.id', 'ASC')
      ->setParameter('division', $division)
      ->getQuery()
      ->getResult();
  }

  /**
   * Returns all pairings for the specified Round objects.
   */
  public function findByRounds(array $rounds) {
    if (count($rounds) == 0) {
      return new ArrayCollection();
    }
    $expr = Criteria::expr();
    $criteria = new Criteria();
    foreach ($rounds as $round) {
      $criteria->orWhere($expr->andX(
        $expr->eq('division', $round->division),
        $expr->eq('round', $round->round)
      ));
      $criteria->orderBy(Pairing::ORDERING);
    }
    return $this->matching($criteria);
  }
}
