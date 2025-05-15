<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nsv\Util\TextSanitizer;

/**
 * A division belongs to a league and contains the pairings and games. It usually also contains
 * teams, in which case those teams are shown in the standings, but it is also possible for teams
 * of other divisions (or no divisions) to participate in a pairing.
 */
#[ORM\Entity]
#[ORM\Table(name: 'staffeln')]
class Division
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private int $id;

  #[ORM\ManyToOne(targetEntity: League::class, inversedBy: 'divisions')]
  #[ORM\JoinColumn(name: "turnier", referencedColumnName: "id")]
  private $league;

  #[ORM\Column(length: 30)]
  private string $name;

  #[ORM\Column(name: "sortid")]
  private ?int $sortId;

  /**
   * The legacy user of the league manager.
   * 
   * TODO: Use WordPress user system.
   * TODO: Allow multiple users to manage a league.
   */
  #[ORM\OneToOne(targetEntity: LegacyUser::class)]
  #[ORM\JoinColumn(name: 'leiter', referencedColumnName: 'id')]
  private $manager;

  /**
   * Number of rounds. If set to null, the league-wide config should be used.
   */
  #[ORM\Column(name: 'runden')]
  private int|null $configRounds = null;

  /**
   * Number of boards. If set to null, the league-wide config should be used.
   */
  #[ORM\Column(name: 'brettzahl')]
  private int|null $configBoardCount = null;

  /**
   * Whether to send eMails with result links to team leaders, so that they
   * can enter results themselves. If set to null, the league-wide configuration
   * should be used.
   */
  #[ORM\Column(name: 'sysEingabelinks')]
  private bool|null $configSendResultLinkMails = null;

  /**
   * Number of teams that will be promoted to a higher league. If set to null,
   * the league-wide configuration should be used.
   */
  #[ORM\Column(name: 'spielAufsteiger')]
  private int|null $configTeamsPromoted = null;

  /**
   * Number of teams that will be relegated. If set to null,
   * the league-wide configuration should be used.
   */
  #[ORM\Column(name: 'spielAbsteiger')]
  private int|null $configTeamsDemoted = null;

  /**
   * Number of teams that face a relegation battle for a higher league. If set
   * to null, the league-wide configuration should be used.
   */
  #[ORM\Column(name: 'spielAufsteigerRelegation')]
  private int|null $configTeamsMaybePromoted = null;

  /**
   * Number of teams that face a relegation battle for a lower league. If set
   * to null, the league-wide configuration should be used.
   */
  #[ORM\Column(name: 'spielAbsteigerRelegation')]
  private int|null $configTeamsMaybeDemoted = null;

  /**
   * Whether to show player numbers in the UI. If set to null, the league-wide
   * configuration should be used.
   */
  #[ORM\Column(name: 'showPassNr')]
  private bool|null $configPlayerNumbers = null;

  /**
   * Whether to show late registrations on the match day page. If set to null,
   * the league-wide configuration should be used.
   */
  #[ORM\Column(name: 'showNachmeldungen')]
  private bool|null $configShowLateRegistrations = null;

  /**
   * Whether to show a preview of the next match day. If set to null,
   * the league-wide configuration should be used.
   */
  #[ORM\Column(name: 'showSpieltagvorschau')]
  private bool|null $configShowNextMatchDay = null;

  /**
   * Whether to show the ranking. If set to null, the league-wide
   * configuration should be used.
   */
  #[ORM\Column(name: 'showTabelle')]
  private bool|null $configShowRanking = null;

  #[ORM\OneToMany(targetEntity: Pairing::class, mappedBy: 'division')]   
  #[ORM\OrderBy(Pairing::ORDERING)]
  private $pairings;

  #[ORM\OneToMany(targetEntity: RoundComment::class, mappedBy: 'division')]   
  private $roundComments;

  public function path() {
    return TextSanitizer::slug($this->name);
  }

  /**
   * Returns all Date entities for this division, keyed by round number.
   * 
   * @return array round => Entity\Date.
   */
  public function dates(): array {
    $dates = [];
    foreach ($this->league->dates as $date) {
      if ($date->division && $date->division != $this) continue;
      if (isset($dates[$date->round])) continue;  // Dates are sorted by most specific first.
      $dates[$date->round] = $date;
    }
    uasort($dates, [Date::class, 'compare']);
    return $dates;
  }

  /**
   * Returns the Round object for the given round.
   */
  public function round(int $round): Round {
    return $this->createRound($round, $this->dates());
  }

  private function createRound(int $round, array $dates): Round {
    return new Round($this, $round, isset($dates[$round]) ? $dates[$round]->date : null);
  }

  /**
   * Returns all Rounds for which a date has been set.
   */
  // TODO: Not really used anymore, replace with something more useful for the remaining use cases.
  public function roundsWithDate(): array {
    $maxRound = $this->config('rounds');
    $rounds = [];
    foreach ($this->dates() as $date) {
      if ($date->round <= $maxRound) {
        $rounds[$date->round] = new Round($this, $date->round, $date->date);
      }
    }
    return $rounds;
  }

  /**
   * Returns all Rounds with at least one pairing.
   */
  public function roundsWithPairing(): array {
    $dates = $this->dates();
    $rounds = [];
    foreach ($this->pairings as $pairing) {
      $round = $pairing->round;
      if (!isset($rounds[$round])) {
        $rounds[$round] = $this->createRound($round, $dates);
      }
    }
    uasort($rounds, [Date::class, 'compare']);
    return $rounds;
  }

  public function teams(): array {
    $teams = [];
    foreach ($this->league->teams as $team) {
      if ($team->division == $this) {
        $teams[] = $team;
      }
    }
    return $teams;
  }

  /**
   * Returns a config property. If the value is set to null on the division
   * the setting on the League will be used.
   */
  public function config($key): mixed {
    $key = 'config' . ucfirst($key);
    $value = $this->$key;
    if ($value === null) {
      return $this->league->$key;
    } else {
      return $value;
    }
  }

  public function uri(): string {
    return $this->league->uri() . $this->path() . '/';
  }

  public function scheduleUri() {
    return $this->league->uri() . $this->path() . "/spielplan/";
  }

  public function statsUri() {
    return $this->league->uri() . '/' . $this->path() . "/statistik";
  }

  public function __call($property, $args) {
    return $this->$property;
  }

  public function __get($property) {
    return $this->$property;
  }

  public function __set($property, $value) {
    $this->$property = $value;
  }
}
