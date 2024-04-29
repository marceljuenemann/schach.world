<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Entity\Team;
use Nsv\League\Entity\Pairing;
use Doctrine\Persistence\ManagerRegistry;

class RankingService
{

  public function __construct(private EntityManagerInterface $leagueEntityManager) {

  }

  /**
   * A temporary method to get started
   */
  public function teamsWithPairings($division, $round) {
    $team_repository = $this->leagueEntityManager->getRepository(Team::class);
    $pairing_repository = $this->leagueEntityManager->getRepository(Pairing::class);
    $teams_division = $team_repository->findByDivision($division);
    $teams_with_pairings = [];
    foreach ($teams_division as $team) {
      $teams_with_pairings[$team->id]['team'] = $team;
      $pairings = $pairing_repository->findByTeamOnlyPairing($team, $round);
      $teams_with_pairings[$team->id]['pairings'] = $pairings;
      $teams_with_pairings[$team->id]['team_points'] = $this->addTeamPoints($team, $pairings);
      $teams_with_pairings[$team->id]['board_points'] = $this->addBoardPoints($team, $pairings);
    }
    // Add team and board points that are won against teams tying for the same ranking spot
    $teams_with_pairings = $this->getTiedTeamData($teams_with_pairings);

    // Sort the teams by team_points and after that by board_points.
    uasort($teams_with_pairings, function ($a, $b) {
      return [$b['team_points'], $b['board_points']] <=> [$a['team_points'], $a['board_points']];
    });
    // Sort the pairings for the crosstable display
    $teams_with_pairings_crosstable = $this->sortPairingsCrosstable($teams_with_pairings);


    //return $teams_division;
    return $teams_with_pairings;
  }

  /**
   * Calculate the team points from the team and its pairings.
   */
  public function addTeamPoints($team, array $pairings) {
    $team_points = (int)0;
    foreach ($pairings as $pairing) {
      if ($pairing->team1->id == $team->id) {
        $team_points += $this->teamPointsFromResult($pairing->result1, $pairing->result2);
      }
      if ($pairing->team2->id == $team->id) {
        $team_points += $this->teamPointsFromResult($pairing->result2, $pairing->result1);
      }
    }
    return $team_points;
  }

  /**
   * Calculate the board points from the team and its pairings.
   */
  public function addBoardPoints($team, array $pairings) {
    $board_points = (float)0;
    foreach ($pairings as $pairing) {
      if ($pairing->team1->id == $team->id) {
        $board_points += $pairing->result1;
      }
      if ($pairing->team2->id == $team->id) {
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
    if ($result1 > $result2) {
      $team_points = 2;
    }
    if ($result1 < $result2) {
      $team_points = 0;
    }
    // This is for the case the pairing was not played at all
    if (empty($result1) && empty($result1)) {
      $team_points = 0;
    }
    if ($result1 == $result2 && !empty($result1)) {
      $team_points = 1;
    }
    return $team_points;
  }

  /**
   * return $teams_with_pairings[]
   * Add the team and board points teams have one against the other ones tied with them.
   * This is only used if multiple teams are tied for a ranking position.
   */
  public function getTiedTeamData($teams_with_pairings) {
    $team_point_values = [];
    $tied_tp_board_point_values = [];
    // Create an array with all occurring team point score.
    // If a team point score already exists, add 1 to the occurrences count.
    foreach ($teams_with_pairings as $team_id => $team) {
      if (!array_key_exists($team['team_points'], $team_point_values)) {
        $team_point_values[$team['team_points']]['score'] = $team['team_points'];
        $team_point_values[$team['team_points']]['occurrences'] = 1;
      } else {
        $team_point_values[$team['team_points']]['occurrences'] += 1;
      }
    }
    // Now check for the team point scores that occur multiple times, if the
    // board points for the involved teams are also identical
    // For the moment I have no better idea than to foreach again through
    // the teams. Some loop multiplication is bound to happen anyway.
   /* foreach ($teams_with_pairings as $team_id => $team) {
      // Check if the team's team point score occurs more than once
      if ($team_point_values[$team['team_points']]['occurrences'] > 1) {
        // We use the board points multiplied by 10 for the array key since
        // the board points can be floats and we cannot use floats as array keys.
        if (!array_key_exists($tied_tp_board_point_values[$team['board_points'] * 10], $tied_tp_board_point_values)) {
          $tied_tp_board_point_values[$team['board_points'] * 10]['score'] = $team['board_points'];
          $tied_tp_board_point_values[$team['board_points'] * 10]['occurrences'] = 1;
          $tied_tp_board_point_values[$team['board_points'] * 10]['team_points'] = $team['team_points'];
        } else {
          $tied_tp_board_point_values[$team['board_points'] * 10]['occurrences'] += 1;
        }
      }
    }*/

    return $teams_with_pairings;
  }

  /**
   * Sort the pairings per team into the crosstable order
   */
  public
  function sortPairingsCrosstable($teams_with_pairings) {
    $teams_with_pairings_crosstable = $teams_with_pairings;
    $standings_grid = [];
    foreach ($teams_with_pairings_crosstable as $key => $team) {
      $standings_grid[$key] = [];
    }
    $prev_team_id = 0;
    foreach ($teams_with_pairings_crosstable as $key => &$team) {
      $team['ranking_position'] = 0;
      $team['crosstable_pairings'] = $standings_grid;
      foreach ($team['pairings'] as $pairing) {
        if ($pairing->team1->id == $team['team']->id) {
          $opponent_id = $pairing->team2->id;
          $team['crosstable_pairings'][$opponent_id]['board_points'] = $pairing->result1;
          $team['crosstable_pairings'][$opponent_id]['round_uri'] = $pairing->division->uri() . $pairing->round;
        }
        if ($pairing->team2->id == $team['team']->id) {
          $opponent_id = $pairing->team1->id;
          $team['crosstable_pairings'][$opponent_id]['board_points'] = $pairing->result2;
          $team['crosstable_pairings'][$opponent_id]['round_uri'] = $pairing->division->uri() . $pairing->round;
        }
      }
      // Also add the ranking number to each team
      $array_position = array_search($key, array_keys($teams_with_pairings_crosstable)) + 1;

      // If the team has the same team and board points as the team before it, it gets the same ranking position
      if (!empty($prev_team_id)) {
        if ($team['team_points'] == $teams_with_pairings_crosstable[$prev_team_id]['team_points'] &&
          $team['board_points'] == $teams_with_pairings_crosstable[$prev_team_id]['board_points']) {
          $team['ranking_position'] = $teams_with_pairings_crosstable[$prev_team_id]['ranking_position'];
        } else {
          $team['ranking_position'] = $array_position;
        }
      } else {
        $team['ranking_position'] = $array_position;
      }

      // We store the current array key for the next iteration in the loop
      $prev_team_id = $key;
    }
    $crosstable_table = $this->create_crosstable_table($teams_with_pairings_crosstable);
    return $teams_with_pairings_crosstable;
  }

  /**
   * create the table structure to send to TWIG.
   */
  public
  function create_crosstable_table($teams_with_pairings_crosstable) {
    $team_count = count($teams_with_pairings_crosstable);

    $crosstable_table['header'] = [
      ['text' => '', 'class' => 'ranking-position'],
      ['text' => 'Mannschaft', 'class' => 'team border-right-bold'],
      ['text' => 'MP', 'class' => 'team-points border-left-bold'],
      ['text' => 'BP', 'class' => 'board-points']
    ];

    // Create header cells for every team numbered from 1 to $team_count
    $crosstable_pairings_numbers = [];
    for ($i = 1; $i <= $team_count; $i++) {
      $crosstable_pairings_numbers[] = ['text' => $i, 'class' => 'pairing-' . $i];
    }

    // insert the numbered th elements into the table header.
    array_splice($crosstable_table['header'], 2, 0, $crosstable_pairings_numbers);

    foreach ($teams_with_pairings_crosstable as $key => $team) {
      $crosstable_table['body'][$key] = [
        [
          'text' => $team['ranking_position'],
          'class' => 'ranking-position'
        ],
        [
          'text' => $team['team']->nameWithNumber(),
          'link' => $team['team']->uri(),
          'class' => 'name border-right-bold',
        ],
        [
          'text' => $team['team_points'],
          'class' => 'team-points border-left-bold'
        ],
        [
          'text' => $team['board_points'],
          'class' => 'board-points'
        ],
      ];
    }

    // Create crosstable cells with pairing results
    $results_grid = [];
    foreach ($teams_with_pairings_crosstable as $key => $team) {
      foreach ($team['crosstable_pairings'] as $pairing_key => $pairing) {
        if (!empty($pairing['board_points'])) {
          $results_grid[$key][] = [
            'text' => $pairing['board_points'],
            'link' => $pairing['round_uri'],
            'title' => 'gegen ' . $teams_with_pairings_crosstable[$pairing_key]['team']->nameWithNumber(),
          ];
        } elseif ($key == $pairing_key) {
          // If the team is paired against itself, make the table cell grey.
          $results_grid[$key][] = [
            'text' => '',
            'class' => 'self-pairing'
          ];
        } else {
          $results_grid[$key][] = [
            'text' => '',
          ];
        }
      }
    }

    // Insert the results grid into the table body
    foreach ($crosstable_table['body'] as $key => &$row) {
      array_splice($row, 2, 0, $results_grid[$key]);
    }

    // reset the array keys of the table body. We don't need the team ids anymore.
    $crosstable_table['body'] = array_values($crosstable_table['body']);


    return $crosstable_table;
  }


}