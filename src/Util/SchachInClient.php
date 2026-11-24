<?php

namespace Nsv\Util;

use Nsv\Dwz\DsbDatabase;

/**
 * Client for reading club data from the schach.in API.
 */
class SchachInClient {

  const HOST = 'https://schach.in/';
  const API_URL = self::HOST . 'zps/%s.json';

  /**
   * Fetches all entities for the given ZPS prefix, e.g. 7, 701, 70156.
   */
  public function fetchZps($zpsPrefix): array {
    $data = json_decode(file_get_contents(sprintf(self::API_URL, $zpsPrefix)), true);
    return array_map(function($entity) {
      return new SchachInEntity($entity);
    }, $data);
  }
}

/**
 * Wrapper around a 'contact' entity as returned by the API.
 */
class SchachInEntity {

  const STATS_FIELDS = [
    'members',
    'members_u25',
    'members_female',
    'avg_age',
    'avg_rating',
  ];

  function __construct(private array $data) {}

  public function isClub() {
    return $this->alive && in_array($this->scope, ['verein', 'verein-schachabteilung']);
  }

  /**
   * Returns a summary of club data.
   */
  public function clubData(): \stdClass {
    $club = new \stdClass();
    $club->zps = $this->currentZps();
    if (!$club->zps) throw new \Exception('No ZPS found');
    $club->districtZps = substr($club->zps, 0, 3);
    $club->name = $this->contact;
    $club->detailsUri = SchachInClient::HOST . $this->identifier;
    $club->dwzUri = DsbDatabase::clubUri($club->zps);
    $club->website = $this->findCategory('url', 'Website', 'identification');
    $club->instagramUri = $this->findCategory('username', 'Instagram', 'username_url');
    $club->venue = $this->venue();

    foreach (self::STATS_FIELDS as $field) {
      $club->stats[$field] = $this->$field;
    }

    return $club;
  }

  /**
   * Returns the club's current ZPS. Clubs that changed districts also list their
   * old ZPS codes, which are not marked as current.
   */
  private function currentZps(): ?string {
    $zpsCodes = array_filter($this->identifiers ?? [], fn($id) => $id['category'] === 'ZPS-Code');
    foreach ($zpsCodes as $zps) {
      if (($zps['current'] ?? null) == '1') return $zps['identifier'];
    }
    // Fall back to the first ZPS in case the API stops sending the current flag.
    return reset($zpsCodes)['identifier'] ?? null;
  }

  /**
   * Returns the first venue found on the entity.
   */
  private function venue() {
    foreach ($this->children ?? [] as $child) {
      foreach ($child['contacts'] as $contact) {
        if ($contact['category'] === 'Veranstaltungsort' && $contact['alive']) {
          return $contact;
        }
      }
    }
    return null;
  }

  private function findCategory($key, $category, $property): mixed {
    if (isset($this->data[$key])) {
      foreach ($this->data[$key] as $element) {
        if ($element['category'] === $category) {
          return $element[$property];
        }
      }
    }
    return null;
  }

  public function __get($key) {
    // Fields like the member statistics are missing for some clubs.
    return $this->data[$key] ?? null;
  }
}
