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

    #[ORM\OneToMany(targetEntity: Pairing::class, mappedBy: 'division')]   
    #[ORM\OrderBy(Pairing::ORDERING)]
    private $pairings;

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
     * Returns the date for a specific round, if one is configured.
     */
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
    public function roundsOnDate(string $date) {
      foreach ($this->dates() as $matchDate) {
        if ($matchDate->date == $date) {
          yield new Round($this, $matchDate->round, $matchDate->date);
        }
      }
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

    public function statsUri() {
      return $this->league->uri() . "?staffel={$this->id}&r=statistik";
    }

    public function matchDayUri($round = '', $pdf = false): string {
      return $this->league->uri() . "?staffel={$this->id}&r=$round" . ($pdf ? '&ausgabe=pdf' : '');
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
