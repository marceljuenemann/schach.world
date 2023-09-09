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
     * Whether to show player numbers in the UI. If set to null, the league-wide
     * configuration should be used.
     */
    #[ORM\Column(name: 'showPassNr')]
    private bool|null $configPlayerNumbers = null;

    /**
     * Whether to show late registrations on the match day page.
     */
    #[ORM\Column(name: 'showNachmeldungen')]
    private bool|null $configShowLateRegistrations = null;

    /**
     * Whether to show a preview of the next match day.
     */
    #[ORM\Column(name: 'showSpieltagvorschau')]
    private bool|null $configShowNextMatchDay = null;

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
     * Returns all Rounds for which a date has been set.
     */
    // TODO: Rename to roundsWithDate()
    // TODO: Use Rounds instead of Dates everywhere possible.
    // TODO: Unit test
    public function rounds(): array {
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
     * Returns the Round object for the given round, if it has a date set.
     */
    // TODO: Return without Date if not set.
    public function round(int $round): Round|null {
      $rounds = $this->rounds();
      return isset($rounds[$round]) ? $rounds[$round] : null;
    }

    /**
     * Returns the date for a specific round, if one is configured.
     */
    // TODO: Use rounds() instead
    // TODO: Unit test.
    public function dateOfRound(int $round): string|null {
      // Note: Dates are ordered by division, so dates for the entire tournament come last.
      foreach ($this->league->dates as $date) {
        if ($date->round != $round) continue;
        if ($date->division && $date->division != $this) continue;
        return $date->date;
      }
      return null;
    }    

    /**
     * Yields all rounds that happen on the given date.
     */
    // TODO: Use rounds() instead
    public function roundsOnDate(string $date) {
      foreach ($this->dates() as $matchDate) {
        if ($matchDate->date == $date) {
          yield new Round($this, $matchDate->round, $matchDate->date);
        }
      }
    }

    public function roundComment(int $round): RoundComment|null {
      foreach ($this->roundComments as $comment) {
        if ($comment->round == $round) {
          // TODO: Create unique index on table.
          return $comment;
        }
      }
      return null;
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

    public function scheduleUri() {
      return $this->league->uri() . $this->path() . "/spielplan/";
    }

    public function statsUri() {
      return $this->league->uri() . "?staffel={$this->id}&r=statistik";
    }

    public function matchDayUri($round = ''): string {
      return $this->league->uri() . "?staffel={$this->id}&r=$round";
    }

    public function matchDayPdfUri($round = ''): string {
      return $this->league->uri() . "?staffel={$this->id}&r=$round&ausgabe=pdf";
    }

    public function matchDayApiUri(int $round): string {
      return "{$this->league->uri()}api/divisions/{$this->path()}/rounds/$round/";
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
