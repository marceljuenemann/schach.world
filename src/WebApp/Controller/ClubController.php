<?php

namespace Nsv\WebApp\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Util\SchachInClient;
use Nsv\WebApp\Core\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/vereine/', name: 'club_')]
class ClubController extends AbstractController {
  const CACHE_NAMESPACE = 'nsvclub';
  const CACHE_KEY = 'districts-schachin';
  const CACHE_EXPIRATION = 60 * 60 * 24;  // 1 day

  private CacheInterface $cache;

  function __construct(
    private EntityManagerInterface $leagueEntityManager,
    private string $projectDir
  ) {
    $this->cache = new FilesystemAdapter(self::CACHE_NAMESPACE);
  }

  #[Route('', name: 'index')]
  public function clubs(): Response {
    $districts = $this->fetchDistrictsCached();
    return $this->render('club/clubs.html.twig', [
      'districts' => $districts,
      'jsonData' => json_encode($districts)
    ]);
  }

  #[Route('api/', name: 'api')]
  public function clubs_api(): Response {
    return new ApiResponse($this->fetchDistrictsCached());
  }

  /**
   * Cached version of fetchDistricts().
   */
  private function fetchDistrictsCached(): array {
    return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
      $item->expiresAfter(self::CACHE_EXPIRATION);
      return $this->fetchDistricts();
    });
  }

  /**
   * Fetches all clubs from schach.in
   */
  private function fetchDistricts(): array {
    $districts = require($this->projectDir . '/public/core/nsv2020/bezirke.php');
    foreach ((new SchachInClient())->fetchZps('7') as $entity) {
      if (!$entity->isClub()) continue;
      $club = $entity->clubData();
      $districts[$club->districtZps]['clubs'][$club->zps] = $club;
    }
    return $districts;
  }
}
