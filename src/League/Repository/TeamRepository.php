<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Player;
use Nsv\League\Entity\Team;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
    $entity = parent::find((int) $id, $lockMode, $lockVersion);
    if (!$entity) {
      throw new NotFoundHttpException("Team not found");
    }
    return $entity;
  }

    /**
     * Find all players for a team, also the inactive ones.
     */
  public function team_all_players($team) {
      return $this->createQueryBuilder('team')
          ->select('team, players')
          ->leftJoin('team.players', 'players')
          ->where('team.id = :team')
          ->setParameter('team', $team->id)
          ->getQuery()
          ->getResult();
  }
}
