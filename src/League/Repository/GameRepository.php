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
    return $this->createQueryBuilder('g')
      // TODO: fetch stuff 
      ->where('g.player1 = :player OR g.player2 = :player')
      ->setParameter('player', $player)
      ->getQuery()
      ->getResult();
  }
}
