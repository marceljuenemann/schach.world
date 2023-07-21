<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
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
}
