<?php

namespace Tests\League;

use Doctrine\DBAL\Connection;

/**
 * Creates a throwaway league (with manager, divisions and teams) directly in the database,
 * and deletes everything belonging to it again in cleanup().
 *
 * Tests are not rolled back (see CLAUDE.md), so tests that modify league data should do
 * so on a TemporaryLeague instead of real fixture data. Cleanup works by league ID, so it
 * also removes rows that were created through the application (e.g. new divisions or players).
 */
final class TemporaryLeague
{
  public const MANAGER_PASSWORD = 'temporary-password';

  public readonly int $id;
  public readonly string $path;
  public readonly int $managerId;
  /** @var int[] */
  public array $divisionIds = [];
  /** @var int[] Team IDs, keyed by division ID. */
  public array $teamIds = [];

  public function __construct(private Connection $db, array $divisionNames = ['Staffel A', 'Staffel B']) {
    $this->path = 'tmp-' . substr(md5(random_bytes(8)), 0, 10);
    $this->managerId = $this->insertUser('Temporary League Manager');

    $db->insert('turniere', [
      'leiter' => $this->managerId,
      'name' => 'Temporary League',
      'organisation' => '7',
      'startjahr' => 2025,
      'runden' => 7,
      'brettzahl' => 8,
      'directory' => $this->path,
      'infomeldung' => '',
      'anmZusatzfelder' => '',
      'spielAufsteiger' => 1,
      'spielAbsteiger' => 1,
      'spielAufsteigerRelegation' => 0,
      'spielAbsteigerRelegation' => 0,
    ]);
    $this->id = (int) $db->lastInsertId();

    foreach ($divisionNames as $index => $name) {
      $db->insert('staffeln', [
        'leiter' => $this->insertUser("Manager $name"),
        'name' => $name,
        'turnier' => $this->id,
        'sortid' => $index,
      ]);
      $divisionId = (int) $db->lastInsertId();
      $this->divisionIds[] = $divisionId;

      $db->insert('mannschaften', [
        'turnier' => $this->id,
        'staffel' => $divisionId,
        'zps' => '70101',
        'name' => "Team $index",
        'mnr' => 1,
        'so_hinweis' => '',
        'mf_name' => 'Captain',
        'mf_email' => 'captain@example.com',
        'mf_telefon' => '0511 123',
        'mf_telefon2' => '',
      ]);
      $this->teamIds[$divisionId] = (int) $db->lastInsertId();
    }
  }

  public function firstTeamId(): int {
    return $this->teamIds[$this->divisionIds[0]];
  }

  public function cleanup(): void {
    $teamIds = $this->db->fetchFirstColumn('SELECT id FROM mannschaften WHERE turnier = ?', [$this->id]);
    $divisionIds = $this->db->fetchFirstColumn('SELECT id FROM staffeln WHERE turnier = ?', [$this->id]);
    $userIds = $this->db->fetchFirstColumn('SELECT leiter FROM staffeln WHERE turnier = ?', [$this->id]);
    $userIds[] = $this->managerId;

    $this->deleteIn('spieler', 'mannschaft', $teamIds);
    $this->deleteIn('zusatzempfaenger', 'mannschaft', $teamIds);
    $this->deleteIn('paarungen', 'staffel', $divisionIds);
    $this->deleteIn('bemerkungen', 'staffel', $divisionIds);
    $this->db->delete('termine', ['turnier' => $this->id]);
    $this->db->delete('mannschaften', ['turnier' => $this->id]);
    $this->db->delete('staffeln', ['turnier' => $this->id]);
    $this->db->delete('turniere', ['id' => $this->id]);
    $this->deleteIn('benutzer', 'id', $userIds);
  }

  private function insertUser(string $name): int {
    $this->db->insert('benutzer', [
      'name' => $name,
      'passwort' => md5(self::MANAGER_PASSWORD),
      'email' => 'manager@example.com',
      'telefon' => '',
      'telefon2' => '',
    ]);
    return (int) $this->db->lastInsertId();
  }

  private function deleteIn(string $table, string $column, array $ids): void {
    if (!$ids) return;
    $this->db->executeStatement(
      "DELETE FROM $table WHERE $column IN (?)",
      [$ids],
      [\Doctrine\DBAL\ArrayParameterType::INTEGER]
    );
  }
}
