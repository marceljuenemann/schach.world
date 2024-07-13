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
  public function search(string $name, ?string $zps, ?bool $active): array {
    return $this->createQueryBuilder('p')
      ->where('p.name LIKE :name')
      ->andWhere('p.zps LIKE :zps')
      ->andWhere('p.status LIKE :status')
      ->orderBy('p.name', 'ASC')
      ->setMaxResults(10)
      // TODO: allow first name search?
      ->setParameter('name', $name.'%')
      ->setParameter('zps', $zps.'%')
      ->setParameter('status', $active ? 'A' : '%')
      ->getQuery()
      ->getResult();
  }
}
