<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Entity\Team;
use Nsv\League\Entity\Pairing;
use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Api\Model\RankingTeam;

class RankingService {

  public function __construct(private EntityManagerInterface $leagueEntityManager) {

  }

  /**
   * A temporary method to get started
   */
  public function teamsWithPairings($division, $round) {
    $pairing_repository = $this->leagueEntityManager->getRepository(Pairing::class);
    $pairings_division = $pairing_repository->findByDivisionWithTeams($division);
    $teams_division = $this->getTeamsFromPairings($pairings_division);

    // The ranking_helper array orders all teams into an array keyed by team points
    // on the first level and by board points on the second level.
    $ranking_helper = [];
    $teams_with_pairings = [];
    foreach ($teams_division as $team) {
      // Now create each team as an object instance of RankingTeam
      $rankingTeam = new RankingTeam();
      $rankingTeam->team = $team;
      $rankingTeam->name = $team->nameWithNumber();
      $rankingTeam->uri = $team->uri();
      $pairings = $this->getPairingsTeamUntilRound($pairings_division, $team, $round);
      $rankingTeam->pairings = $pairings;
      $rankingTeam->team_points = $this->addTeamPoints($team, $pairings);
      $rankingTeam->board_points = $this->addBoardPoints($team, $pairings);
      $teams_with_pairings[$team->id] = $rankingTeam;
      $ranking_helper[$rankingTeam->team_points][(string) $rankingTeam->board_points][$team->id] = $team;
    }

    // Now apply direct comparison to teams that are tied by team and board points
    // inside the $ranking_helper array
    foreach ($ranking_helper as &$mptied) {
      foreach ($mptied as &$bptied) {
        // If there is more than one team inside
        // a $bptied group, those teams are tied and
        // we need to apply a direct comparison for fine ranking
        // We use the same basic method as in the legacy tabelle.inc.php
        if (count($bptied) > 1) {
          $bptied = $this->directComparison($bptied);
        }
      }
    }

    // Sort the teams by team_points and after that by board_points.
    uasort($teams_with_pairings, function ($a, $b) {
      return [$b->team_points, $b->board_points] <=> [$a->team_points, $a->board_points];
    });
    // Sort the pairings for the crosstable display
    $teams_with_pairings = $this->sortPairingsCrosstable($teams_with_pairings);

    //return $teams_division;
    return $teams_with_pairings;
  }

  /**
   * Get the teams from the pairings of the division.
   * We get the teams this way because sometimes the entry for the
   * division in the team does not match the division we are creating the
   * rankings for.
   */
  public function getTeamsFromPairings($pairings_division) {
    $teams_division = [];

    $teams_already_added_ids = [];
    $teams_division = [];
    foreach ($pairings_division as $pairing) {
      if (!in_array($pairing->team1->id, $teams_already_added_ids)) {
        $teams_division[] = $pairing->team1;
        $teams_already_added_ids[] = $pairing->team1->id;
      }
      if (!in_array($pairing->team2->id, $teams_already_added_ids)) {
        $teams_division[] = $pairing->team2;
        $teams_already_added_ids[] = $pairing->team2->id;
      }
    }
    return $teams_division;
  }

  /**
   * Get all pairings that the team has played up to
   * the round that we are viewing.
   */
  public function getPairingsTeamUntilRound($pairings_division, $team, $round) {
    $pairings_team = [];
    foreach ($pairings_division as $pairing) {
      if (($pairing->team1==$team || $pairing->team2==$team) && $pairing->round<=$round) {
        $pairings_team[] = $pairing;
      }
    }
    return $pairings_team;
  }

  /**
   * Calculate the team points from the team and its pairings.
   */
  public function addTeamPoints($team, array $pairings) {
    $team_points = (int) 0;
    foreach ($pairings as $pairing) {
      if ($pairing->team1->id==$team->id) {
        $team_points += $this->teamPointsFromResult($pairing->result1, $pairing->result2);
      }
      if ($pairing->team2->id==$team->id) {
        $team_points += $this->teamPointsFromResult($pairing->result2, $pairing->result1);
      }
    }
    return $team_points;
  }

  /**
   * Calculate the board points from the team and its pairings.
   */
  public function addBoardPoints($team, array $pairings) {
    $board_points = (float) 0;
    foreach ($pairings as $pairing) {
      if ($pairing->team1->id==$team->id) {
        $board_points += $pairing->result1;
      }
      if ($pairing->team2->id==$team->id) {
        $board_points += $pairing->result2;
      }
    }
    return $board_points;
  }

  /**
   * A separate method for calculating team points.
   * Saves repetition of this code.
   */
  public function teamPointsFromResult($result1, $result2) {
    // Team 1 wins
    if ($result1 > $result2) {
      $team_points = 2;
    }
    // Team 1 loses
    if ($result1 < $result2) {
      $team_points = 0;
    }
    // This is for the case the pairing was not played at all
    if (empty($result1) && empty($result1)) {
      $team_points = 0;
    }
    // Draw
    if ($result1==$result2 && !empty($result1)) {
      $team_points = 1;
    }
    return $team_points;
  }

  /**
   * Do a direct comparison to sort (fine ranking) tied teams.
   */
  public function directComparison($bptied) {
    // We are not interested in the points the teams won totally,
    // we want only the points they won against the teams they are tied with.
    $directComparisonPoints = [];
    foreach ($bptied as $a => $tied_team) {
      // loop over all possible matchups
      // we can follow the old method direkterVergleich() quite closely here.
      // We build another $ranking_helper() array, only this time we only use
      // the points gained against the tied teams like described above.
      for ($a = 0; $a < count($bptied); ++$a) {

      }
    }
  }

  /**
   * Return the team points a team won against another team
   * in the current season in the current division
   */
  public function teamPointsAgainstTeam($team, $round) {

  }

  /**
   * Return the board points a team won against another team
   * in the current season in the current division
   */
  public function boardPointsAgainstTeam($team, $round) {

  }

  /**
   * Sort the pairings per team into the crosstable order
   */
  public function sortPairingsCrosstable($teams_with_pairings) {
    $teams_with_pairings_crosstable = $teams_with_pairings;
    $standings_grid = [];
    foreach ($teams_with_pairings_crosstable as $key => $team) {
      // Initialize all games with 999 board points, so this
      // is the number for not yet played and it is not empty so TWIG does not
      // throw an error when calling it.
      $standings_grid[$key] = ['board_points' => 999];
    }
    $prev_team_id = 0;
    foreach ($teams_with_pairings_crosstable as $key => &$team) {
      $team->ranking_position = 0;
      $team->crosstable_pairings = $standings_grid;
      // Mark the game against oneself with 888 board points.
      $team->crosstable_pairings[$team->team->id]['board_points'] = 888;
      foreach ($team->pairings as $pairing) {
        if ($pairing->team1->id==$team->team->id) {
          $opponent_id = $pairing->team2->id;
          $team->crosstable_pairings[$opponent_id]['board_points'] = $pairing->result1;
          $team->crosstable_pairings[$opponent_id]['round_uri'] = $pairing->division->uri() . $pairing->round;
          $team->crosstable_pairings[$opponent_id]['title_text'] = 'gegen ' . $pairing->team2->nameWithNumber();
        }
        if ($pairing->team2->id==$team->team->id) {
          $opponent_id = $pairing->team1->id;
          $team->crosstable_pairings[$opponent_id]['board_points'] = $pairing->result2;
          $team->crosstable_pairings[$opponent_id]['round_uri'] = $pairing->division->uri() . $pairing->round;
          $team->crosstable_pairings[$opponent_id]['title_text'] = 'gegen ' . $pairing->team1->nameWithNumber();
        }
      }
      // Also add the ranking number to each team
      $array_position = array_search($key, array_keys($teams_with_pairings_crosstable)) + 1;

      // If the team has the same team and board points as the team before it, it gets the same ranking position
      if (!empty($prev_team_id)) {
        if ($team->team_points==$teams_with_pairings_crosstable[$prev_team_id]->team_points &&
          $team->board_points==$teams_with_pairings_crosstable[$prev_team_id]->board_points) {
          $team->ranking_position = $teams_with_pairings_crosstable[$prev_team_id]->ranking_position;
        } else {
          $team->ranking_position = $array_position;
        }
      } else {
        $team->ranking_position = $array_position;
      }

      // We store the current array key for the next iteration in the loop
      $prev_team_id = $key;
    }
    //$crosstable_table = $this->create_crosstable_table($teams_with_pairings_crosstable);
    return $teams_with_pairings_crosstable;
  }


}