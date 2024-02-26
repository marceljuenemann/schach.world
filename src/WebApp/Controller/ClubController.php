<?php

namespace Nsv\WebApp\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\DsbDatabase;
use Nsv\League\Core\Encoding;
use Nsv\Util\TextSanitizer;
use Nsv\WebApp\Core\ApiResponse;
use SimpleXMLElement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

const CACHE_EXPIRATION = 60 * 60 * 24;  // 1 day
const SCHACH_IN_URL = 'https://schach.in/';
const SCHACH_IN_GEOJSON = SCHACH_IN_URL . 'niedersachsen.geojson';

// Schach.in does not provide ZPS in the export, so we try to match by name...
const SCHACH_IN_HACKS = [
  'mtv-salzhausen' => 'sabt-mtv-salzhausen',
  'sc-schwarzer-springer-bad-zwischenahn' => 'sc-schw-springer-bad-zwischenahn' 
];

#[Route('/vereine/', name: 'club_')]
class ClubController extends AbstractController {

  private CacheInterface $cache;

  function __construct(
    private EntityManagerInterface $leagueEntityManager,
    private string $projectDir
  ) {
    $this->cache = new FilesystemAdapter('nsvclub');
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
    $data = $this->fetchDistricts();
    return new ApiResponse($data);
  }

  /**
   * Cached version of fetchDistricts().
   */
  private function fetchDistrictsCached(): array {
    return $this->cache->get('districts', function (ItemInterface $item): array {
      $item->expiresAfter(CACHE_EXPIRATION);
      return $this->fetchDistricts();
    });
  }

  /**
   * Fetches all districts with full club data.
   */
  private function fetchDistricts(): array {
    $districts = require($this->projectDir . '/public/core/nsv2020/bezirke.php');
    $clubs = $this->fetchClubs();
    $mapData = $this->fetchMapData();
    $websites = $this->fetchWebsites();
    foreach ($clubs as $club) {
      $slug = TextSanitizer::slug($club['name']);
      if (isset($mapData[$slug])) {
        $club['properties'] = $mapData[$slug];
        $club['detailsUri'] = SCHACH_IN_URL . $club['properties']->identifier;
      }
      if (isset($websites[$club['zps']]['website'])) {
        $club['website'] = $websites[$club['zps']]['website'];
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
    $content = file_get_contents(SCHACH_IN_GEOJSON);
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

  /**
   * Fetches websites from legacy mivis XML
   * 
   * TODO: Hopefully can fetch data from schach.in as well.
   */
  private function fetchWebsites() {
    $xmlstr = file_get_contents($this->projectDir . '/data/clubs/legacy-mivis-export.xml');
    $xml = new SimpleXMLElement($xmlstr);

    $vereine = array();
    foreach($xml->vereine->verein as $verein){
      $zps = "" . $verein->vkz;
      if ( !strlen($zps) || substr ( $zps, 2, 1 ) === "0" ) continue;
      
      $homepage = "";
      foreach ( $verein->spiellokale->spiellokal as $spiellokal ){
        if ( strlen ( $spiellokal->url ) ){
          $homepage = "".$spiellokal->url;
        }
      }
      if ( strlen ( $verein->homepage ) ) {
        $homepage = "".$verein->homepage;
      }
      if ( strlen ( $homepage ) && strpos ( $homepage, "http://" ) === false && strpos ( $homepage, "https://" ) === false )
        $homepage = "http://$homepage";
      if ($homepage) {
        $vereine[$zps]["website"] = $homepage;
      }
    }
    return $vereine;
  }
}
