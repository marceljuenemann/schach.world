<?php

namespace Nsv\Dwz\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\Dwz\Entity\Player;

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

  /** Finds players filtered by various parameters */
  public function search(string $name, string $zps = '', bool $active = true): array {
    return $this->createQueryBuilder('p')
      ->where('p.name LIKE :name')
      ->andWhere('p.zps LIKE :zps')
      ->andWhere('p.status LIKE :status')
      ->orderBy('p.name', 'ASC')
      ->setMaxResults(10)
      // TODO: allow first name search?
      ->setParameter('name', $name.'%')
      ->setParameter('zps', $zps.'%')
      ->setParameter('status', $active ? Player::STATUS_ACTIVE : '%')
      ->getQuery()
      ->getResult();
  }

  /**
   * First tries to find players with the $preferredZps, then fills up with
   * other players.
   */
  public function searchWithPreferredZps(string $name, string $preferredZps, bool $active = true, int $limit = 10): array {
    $players = $this->search($name, $preferredZps, $active);
    if (count($players) < $limit) {
      foreach ($this->search($name, '', $active) as $player) {
        if (count($players) >= $limit) break;
        if (array_search($player, $players) !== false) continue;
        $players[] = $player;
      }
    }
    return $players;
  }
}
