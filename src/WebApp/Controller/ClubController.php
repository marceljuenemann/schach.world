<?php

namespace Nsv\WebApp\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\DsbDatabase;
use Nsv\League\Core\Encoding;
use Nsv\WebApp\Core\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vereine/', name: 'club_')]
class ClubController extends AbstractController {

  function __construct(
    private EntityManagerInterface $leagueEntityManager,
    private string $projectDir
  ) {}

  #[Route('', name: 'index')]
  public function clubs(): Response {
    $districts = $this->fetchDistricts();
    return $this->render('club/clubs.html.twig', ['districts' => $districts]);
  }

  #[Route('api/', name: 'api')]
  public function clubs_api(): Response {
    $data = $this->fetchDistricts();
    return new ApiResponse($data);
  }

  /**
   * Fetches all districts with full club data.
   */
  private function fetchDistricts(): array {
    $districts = require($this->projectDir . '/public/core/nsv2020/bezirke.php');
    $clubs = $this->fetchClubs();
    foreach ($clubs as $club) {
      $districts[$club['district']]['clubs'][$club['zps']] = $club;
    }
    return $districts;
  }

  /**
   * Fetches clubs from the DWZ database.
   */
  private function fetchClubs(): array {
    $sql = " 
      SELECT *
        FROM dwz_vereine
      WHERE ZPS LIKE '7%' AND Verband <> '700' 
    ";
    $stmt = $this->leagueEntityManager->getConnection()->prepare($sql);
    $clubs = $stmt->executeQuery()->fetchAllAssociative();
    return array_map(function($club) {
      return [
        'zps' => $club['ZPS'],
        'name' => Encoding::utf8_encode($club['Vereinname']),  // League DB connection is not UTF-8
        'district' => $club['Verband'],
        'dwzUri' => DsbDatabase::clubUri($club['ZPS'])
      ];
    }, $clubs);
  }
}
