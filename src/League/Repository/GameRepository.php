<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Game;
use Nsv\League\Entity\Player;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Game::class);
  }

  /**
   * Returns all games of the specified player. Populates the pairings and opponent as well.
   */
  public function findByPlayer(Player $player): array {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->select('g, p, t1, t2, s1, s2')
      ->from(Game::class, 'g')
      ->join('g.pairing', 'p')
      ->join('p.team1', 't1')
      ->join('p.team2', 't2')
      // leftJoin to allow NULL players.
      ->leftJoin('g.player1', 's1')
      ->leftJoin('g.player2', 's2')
      ->where('g.player1 = :player OR g.player2 = :player')
      ->orderBy('p.round, g.board')
      ->setParameter('player', $player)
      ->getQuery()
      ->getResult();
  }
}
