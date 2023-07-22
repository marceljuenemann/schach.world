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
    #[ORM\OrderBy(["round" => "ASC", "host" => "ASC", "id" => "ASC"])]
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
      // TODO: Sort by date?
      return $dates;
    }

    /**
     * Returns the Entity\Date closest to the given date.
     */
    // TODO: Convert to return Round object?
    public function closestMatchDate(string $date): Date|null {
      $date = date_create($date);
      $closestDate = null;
      $closestDiff = null;
      foreach ($this->dates() as $round => $matchDate) {
        $interval = date_diff(date_create($matchDate->date), $date);
        $diff = (int) $interval->format('%R%a'); // +/- number of days
        // TODO: handle multiple rounds on one date correctly.
        if ($closestDiff === null || abs($diff) < abs($closestDiff)) {
          $closestDate = $matchDate;
          $closestDiff = $diff;
        }
      }
      return $closestDate;
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

    public function matchDayUri($round = ''): string {
      return $this->league->uri() . "?staffel={$this->id}&r=$round";
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
