<?php

namespace Nsv\Registration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\Registration\Entity\PlayerRegistration;

/**
 * @extends ServiceEntityRepository<PlayerRegistration>
 */
class PlayerRegistrationRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, PlayerRegistration::class);
  }

  /**
   * Fetches all registrations together with their DWZ db entries.
   */
  public function findByTournament(string $tournament): array {
    return $this->getEntityManager()
      ->createQueryBuilder()
      ->select('p, d, c')
      ->from(PlayerRegistration::class, 'p')
      ->leftJoin('p.dwzPlayer', 'd')
      ->leftJoin('d.club', 'c')
      ->where('p.tournament LIKE :tournament')
      ->setParameter('tournament', $tournament)
      ->getQuery()
      ->getResult();
  }
}
