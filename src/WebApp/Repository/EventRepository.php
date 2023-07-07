<?php

namespace Nsv\WebApp\Repository;

use Nsv\WebApp\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 */
class EventRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
    parent::__construct($registry, Event::class);
  }

  /** @return Array<Event> */
  public function getUpcoming(): Array
  {
    return $this->_em->createQuery('
        SELECT e 
        FROM Nsv\WebApp\Entity\Event e
        WHERE e.date >= CURRENT_DATE()
        AND e.isApproved = 1
        ORDER BY e.date
      ')->getResult();
  }
}
