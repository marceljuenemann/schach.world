<?php

namespace Nsv\WebApp\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\DsbDatabase;
use Nsv\League\Core\Encoding;
use Nsv\Util\TextSanitizer;
use Nsv\WebApp\Core\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

// Schach.in does not provide ZPS in the export, so we try to match by name...
const SCHACH_IN_HACKS = [
  'mtv-salzhausen' => 'sabt-mtv-salzhausen',
  'sc-schwarzer-springer-bad-zwischenahn' => 'sc-schw-springer-bad-zwischenahn' 
];

#[Route('/vereine/', name: 'club_')]
class ClubController extends AbstractController {

  function __construct(
    private EntityManagerInterface $leagueEntityManager,
    private string $projectDir
  ) {}

  #[Route('', name: 'index')]
  public function clubs(): Response {
    // TODO: Add cache
    $districts = $this->fetchDistricts();
    return $this->render('club/clubs.html.twig', [
      'districts' => $districts,
      'jsonData' => json_encode($districts)
    ]);
  }

  #[Route('api/', name: 'api')]
  public function clubs_api(): Response {
    $data = $this->fetchDistricts();
    //$data = $this->fetchMapData();
    return new ApiResponse($data);
  }

  /**
   * Fetches all districts with full club data.
   */
  private function fetchDistricts(): array {
    $districts = require($this->projectDir . '/public/core/nsv2020/bezirke.php');
    $clubs = $this->fetchClubs();
    $mapData = $this->fetchMapData();
    foreach ($clubs as $club) {
      $slug = TextSanitizer::slug($club['name']);
      if (isset($mapData[$slug])) {
        $club['properties'] = $mapData[$slug];
      }
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

  /**
   * Fetches schach.in map data.
   */
  private function fetchMapData() {
    // TODO: Load from prod.
    $content = file_get_contents(dirname(__FILE__) . '/tmp.json');
    foreach (json_decode($content)->features as $feature) {
      // TODO: Ask schach.in to provide ZPS to avoid matching by name.
      $slug = TextSanitizer::slug($feature->properties->org);
      $feature->properties->coordinates = array_reverse($feature->geometry->coordinates);
      $clubs[$slug] = $feature->properties;
    }
    foreach (SCHACH_IN_HACKS as $from => $to) {
      $clubs[$to] = $clubs[$from];
    }
    return $clubs;
  }
}
