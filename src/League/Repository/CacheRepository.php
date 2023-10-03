<?php

namespace Nsv\League\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\CacheEntry;
use Nsv\League\Entity\Division;

/**
 * @extends ServiceEntityRepository<CacheEntry>
 */
class CacheRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, CacheEntry::class);
  }

  /**
   * Returns an object from the cache. In case of a cache miss, the value is computed
   * by invoking the callback function and then stored in the cache.
   */
  public function getOrCompute(string $type, Division $division, int $round, callable $callback): mixed {
    $val = $this->get($type, $division, $round);
    if ($val) return $val;
    $val = $callback();

    $entry = new CacheEntry();
    $entry->league = $division->league;
    $entry->division = $division;
    $entry->round = $round;
    $entry->type = $type;
    $entry->value = serialize($val);
    if (strlen($entry->value) < 65000) {  // Fix for too large cache entries.
      $this->getEntityManager()->persist($entry);
      $this->getEntityManager()->flush();
    }
    return $val;
  }

  public function get(string $type, Division $division, int $round): mixed {
    $entries = $this->findBy([
      'type' => $type,
      'division' => $division,
      'round' => $round
    ]);
    if (!count($entries)) return null;
    return unserialize($entries[0]->value);
  }
}
