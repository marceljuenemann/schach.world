<?php

namespace Nsv\League\Api\Service;

use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Pairing;
use Nsv\League\Core\Result;
use Nsv\League\Entity\Team;
use Nsv\League\Api\Service\DivisionService;

class StatisticsService
{
  public function __construct(
    private ManagerRegistry $doctrine, private Encoding $encoding, private DivisionService $divisionService
  )
  {
    $this->entityManager = $this->doctrine->getManager('league');
  }

  /**
   * Return all games that have been played in a divison during
   * the season.
   */
  public function all_games_division($division)
  {

    $pairing_repository = $this->doctrine->getRepository(Pairing::class);
    $data = $pairing_repository->findAllGamesDivision($division);

    $all_games = [];
    $all_games_ids = [];

    foreach ($data as $key => $pairing) {
      $games = $pairing->games->getValues();
      foreach ($games as $key2 => $game) {
        if (!in_array($game->id, $all_games_ids)) {
          $all_games_ids[] = $game->id;
          $all_games[] = $game;
        }
      }
    }
    return $all_games;
  }

  public function active_players_division($all_games)
  {
    $active_players = [];
    $active_players_ids = [];
    foreach ($all_games as $key => $game) {
      if (is_object($game->player1)) {
        // Make sure we add the players only once to our array
        if (!in_array($game->player1->id, $active_players_ids)) {
          $active_players_ids[] = $game->player1->id;
          $active_players[$game->player1->id]['player'] = $game->player1;
          if (!isset($active_players[$game->player1->id]['points'])) {
            $active_players[$game->player1->id]['points'] = (float)0.0;
          }
          if (!isset($active_players[$game->player1->id]['draws'])) {
            $active_players[$game->player1->id]['draws'] = (int)0;
          }
        }
      }
      if (is_object($game->player2)) {
        if (!in_array($game->player2->id, $active_players_ids)) {
          // Make sure we add the players only once to our array
          $active_players_ids[] = $game->player2->id;
          $active_players[$game->player2->id]['player'] = $game->player2;
          if (!isset($active_players[$game->player2->id]['points'])) {
            $active_players[$game->player2->id]['points'] = (float)0.0;
          }
          if (!isset($active_players[$game->player2->id]['draws'])) {
            $active_players[$game->player2->id]['draws'] = (int)0;
          }
        }
      }
    }
    return $active_players;
  }

  /**
   * Get all teams active in the division and add
   * active players and all players (including passive ones) as separate arrays.
   */
  public function active_teams_with_players($active_players)
  {
    $team_repository = $this->doctrine->getRepository(Team::class);
    $active_teams_with_players = [];
    // Collect all active teams and add active players to them
    foreach ($active_players as $player) {
      $team = $player['player']->team;
      $team_id = $player['player']->team->id;
      $player_id = $player['player']->id;
      if (!array_key_exists($team_id, $active_teams_with_players)) {
        $active_teams_with_players[$team_id]['team'] = $team;
      }
      $active_teams_with_players[$team_id]['active_players'][$player_id] = $player['player'];
    }

    // Get all players for a team, also the passive ones
    foreach ($active_teams_with_players as $key => &$team) {

      $team_with_players = $team_repository->team_all_players($team['team']);
      $team_players = reset($team_with_players)->players->getValues();
      foreach ($team_players as $team_player) {
        $team['all_players'][] = $team_player;
      }
    }
    // Get the topX Players that are registered to the regular boards.
    // If the League plays with 8 boards per team, it is the top 8 registered players.
    foreach ($active_teams_with_players as $key => &$team) {
      // First get the number of boards the league is played with
      $board_count = $team['team']->league->boardCount;
      $team_with_players = $team_repository->team_all_players($team['team']);
      $team_players = reset($team_with_players)->players->getValues();
      foreach ($team_players as $team_player) {
        if ($team_player->number <= $board_count) {
          $team['top_x_players'][] = $team_player;
        }
      }
    }

    return $active_teams_with_players;
  }

  /**
   * Return all active players with their played games as a subarray
   * for each player.
   */
  public function active_players_with_games($active_players, $all_games)
  {
    foreach ($active_players as $key => &$player) {
      $player_games_ids = [];
      if (!isset($player['games'])) {
        foreach ($all_games as $game) {
          if (is_object($game->player1) && $game->player1->id == $player['player']->id) {

            // It is probably not necessary but we check to only add a game once to
            // the player's games.
            if (!in_array($game->id, $player_games_ids)) {
              $player_games_ids[] = $game->id;
              $player['games'][] = $game;
              $result1 = $this->encoding->utf8_encode($game->result1);
              if ($result1 == 1) {
                $player['points'] += 1.0;
              }
              if ($result1 == Result::UNICODE_DRAW) {
                $player['points'] += 0.5;
                $player['draws'] += 1;
              }
            }
          }
          if (is_object($game->player2) && $game->player2->id == $player['player']->id) {
            if (!in_array($game->id, $player_games_ids)) {
              $player_games_ids[] = $game->id;
              $player['games'][] = $game;
              $result2 = $this->encoding->utf8_encode($game->result2);
              if ($result2 == 1) {
                $player['points'] += 1.0;
              }
              if ($result2 == Result::UNICODE_DRAW) {
                $player['points'] += 0.5;
                $player['draws'] += 1;
              }
            }
          }
        }
      }
    }

    $active_players_with_games = $active_players;
    return $active_players_with_games;
  }

  /**
   * Calculate the DWZ averages for the table
   */
  public function teams_dwz_calculation($active_teams_with_players)
  {
    $active_teams_with_dwz = $active_teams_with_players;
    $dwz_data = [];

    foreach ($active_teams_with_dwz as $key => &$team) {
      // Add up all DWZ numbers from the active players
      $dwz_data[$key]['active_dwz_sum'] = (int)0;
      foreach ($team['active_players'] as $player) {
        $dwz = $player->dwz;
        if (empty($dwz)) {
          $dwz_data[$key]['active_dwz_sum'] += 700;
        } else {
          $dwz_data[$key]['active_dwz_sum'] += $player->dwz;
        }
      }
      // calculate the active DWZ average
      $players_count = count($team['active_players']);
      $dwz_active_average = $dwz_data[$key]['active_dwz_sum'] / $players_count;
      $team['active_dwz_average'] = round($dwz_active_average);

      // Add up all DWZ numbers from all players (including passive)
      $dwz_data[$key]['all_dwz_sum'] = (int)0;
      foreach ($team['all_players'] as $player) {
        $dwz = $player->dwz;
        if (empty($dwz)) {
          $dwz_data[$key]['all_dwz_sum'] += 700;
        } else {
          $dwz_data[$key]['all_dwz_sum'] += $player->dwz;
        }
      }
      // calculate all player DWZ average
      $all_players_count = count($team['all_players']);
      $dwz_all_average = $dwz_data[$key]['all_dwz_sum'] / $all_players_count;
      $team['all_dwz_average'] = round($dwz_all_average);

      // Add up all DWZ numbers from the topX players
      // These are the players that are registered to a regular board.
      // If the League plays with 8 boards per team, it is the top 8 registered players.

      $dwz_data[$key]['top_dwz_sum'] = (int)0;
      foreach ($team['top_x_players'] as $player) {
        $dwz = $player->dwz;
        if (empty($dwz)) {
          $dwz_data[$key]['top_dwz_sum'] += 700;
        } else {
          $dwz_data[$key]['top_dwz_sum'] += $player->dwz;
        }
      }
      // calculate top player DWZ average
      $top_players_count = count($team['top_x_players']);
      $dwz_top_average = $dwz_data[$key]['top_dwz_sum'] / $top_players_count;
      $team['top_x_dwz_average'] = round($dwz_top_average);

      // Calculate the average age for the active players
      $dwz_data[$key]['active_age_sum'] = (int)0;
      $aged_players_count = (int)0;
      foreach ($team['active_players'] as $player) {
        $birthyear = $player->birth;
        $date = new \DateTime();
        $timezone = new \DateTimeZone('Europe/Berlin');
        $date->setTimezone($timezone);
        $current_year = $date->format('Y');


        if (!empty($birthyear)) {
          $dwz_data[$key]['active_age_sum'] += $current_year - $birthyear;
          $aged_players_count += 1;
        }
      }
      // calculate the active age average
      // It could be that we have no age for any of the players.
      if (!empty($aged_players_count)) {
        $age_active_average = $dwz_data[$key]['active_age_sum'] / $aged_players_count;
        $team['active_age_average'] = round($age_active_average);
      }
    }
    // Sort the teams by the highest DWZ average
    uasort($active_teams_with_dwz, function ($a, $b) {
      return [$b['active_dwz_average']] <=> [$a['active_dwz_average']];
    });

    return $active_teams_with_dwz;
  }

  /**
   * Sort the players by points
   */
  public function players_sorted_by_points($active_players_with_games)
  {
    uasort($active_players_with_games, function ($a, $b) {
      return [$b['points']] <=> [$a['points']];
    });
    return $active_players_with_games;
  }

  /**
   * Sort the players by draws
   */
  public function players_sorted_by_draws($active_players_with_games)
  {
    uasort($active_players_with_games, function ($a, $b) {
      return [$b['draws']] <=> [$a['draws']];
    });
    return $active_players_with_games;
  }

  /**
   * Create the table array for DWZ statistics that
   * is sent to the template in the controller.
   */
  public function create_dwz_statistics_table($division)
  {
    $all_games = $this->all_games_division($division);
    $active_players = $this->active_players_division($all_games);
    $active_players_with_games = $this->active_teams_with_players($active_players);
    $dwz_calculation = $this->teams_dwz_calculation($active_players_with_games);

    // Get the board count
    $first_team = reset($dwz_calculation);
    $board_count = $first_team['team']->league->boardCount;

    $dwz_table = [];

    $dwz_table['header'] = [
      [
        'text' => 'Mannschaft',
        'class' => 'team'
      ],
      [
        'text' => 'Eingesetzte',
        'class' => 'active',
        'title' => 'DWZ Durchschnitt der Spieler, die tatsächlich gespielt haben. Spieler ohne DWZ werden als DWZ 700 gewertet.'
      ],
      [
        'text' => 'Top ' . $board_count,
        'class' => 'top',
        'title' => 'Durchschnittliche DWZ der Stammspieler. Spieler ohne DWZ werden als DWZ 700 gewertet.'
      ],
      [
        'text' => 'Alle Spieler',
        'class' => 'all',
        'title' => 'Durchschnittliche DWZ von allen gemeldeten Spielern. Spieler ohne DWZ werden als DWZ 700 gewertet.'
      ],
      [
        'text' => 'Alter ∅',
        'class' => 'age',
        'title' => 'Durchschnittliches Alter der Spieler, die tatsächlich gespielt haben.'
      ],
    ];

    $average_sums = [
      'dwz_active' => (int)0,
      'dwz_top' => (int)0,
      'dwz_all' => (int)0,
      'age' => (int)0,
    ];

    // Create the table body
    foreach ($dwz_calculation as $key => $team) {
      $team_name = $team['team']->name;
      $team_uri = $team['team']->uri();
      $dwz_active = $team['active_dwz_average'];
      $dwz_top = $team['top_x_dwz_average'];
      $dwz_all = $team['all_dwz_average'];
      $age = $team['active_age_average'];

      // Add each team's value to the sum so we can
      // calculate the average for the last row.
      $average_sums['dwz_active'] += $dwz_active;
      $average_sums['dwz_top'] += $dwz_top;
      $average_sums['dwz_all'] += $dwz_all;
      $average_sums['age'] += $age;

      $dwz_table['body'][] = [
        [
          'text' => $team_name,
          'link' => $team_uri,
          'class' => 'team'
        ],
        [
          'text' => $dwz_active,
          'class' => 'active'
        ],
        [
          'text' => $dwz_top,
          'class' => 'top'
        ],
        [
          'text' => $dwz_all,
          'class' => 'all'
        ],
        [
          'text' => $age,
          'class' => 'age'
        ],
      ];
    }

    $team_count = count($dwz_calculation);
    $average_values = [
      'dwz_active' => round($average_sums['dwz_active'] / $team_count),
      'dwz_top' => round($average_sums['dwz_top'] / $team_count),
      'dwz_all' => round($average_sums['dwz_all'] / $team_count),
      'age' => round($average_sums['age'] / $team_count),
    ];

    $dwz_table['body'][] = [
      [
        'text' => 'Durchschnitt:',
        'class' => 'average-active format-bold'
      ],
      [
        'text' => $average_values['dwz_active'],
        'class' => 'average-active format-bold'
      ],
      [
        'text' => $average_values['dwz_top'],
        'class' => 'average-top format-bold'
      ],
      [
        'text' => $average_values['dwz_all'],
        'class' => 'average-all format-bold'
      ],
      [
        'text' => $average_values['age'],
        'class' => 'average-age format-bold'
      ],
    ];

    return $dwz_table;


    $ulla = 3;
  }

  /**
   * Create the table array for topscorers that
   * is sent to the template in the controller.
   */
  public function create_topscorer_table($division)
  {
    $all_games = $this->all_games_division($division);
    $active_players = $this->active_players_division($all_games);
    $active_players_with_games = $this->active_players_with_games($active_players, $all_games);
    $players_with_games_by_points = $this->players_sorted_by_points($active_players_with_games);
    $top_ten_scorers = array_slice($players_with_games_by_points, 0, 10, true);

    $topscorer_table = [];

    $topscorer_table['header'] = [
      ['text' => 'Name', 'class' => 'name'],
      ['text' => 'DWZ', 'class' => 'rating-national'],
      ['text' => 'Mannschaft', 'class' => 'team'],
      ['text' => 'Brett', 'class' => 'board'],
      ['text' => 'Partien', 'class' => 'games'],
      ['text' => 'Punkte', 'class' => 'points']
    ];

    foreach ($top_ten_scorers as $key => $player) {
      $first_name = $player['player']->firstName;
      $last_name = $player['player']->lastName;
      $player_uri = $player['player']->uri();
      $dwz = $player['player']->dwz ?? '';
      $team = $player['player']->team->name;
      $team_uri = $player['player']->team->uri();
      $board = $player['player']->number ?? '';
      $games_count = count($player['games']);
      $points = $player['points'];

      $topscorer_table['body'][] = [
        [
          'text' => $first_name . ' ' . $last_name,
          'link' => $player_uri,
          'class' => 'name'
        ],
        [
          'text' => $dwz,
          'class' => 'dwz'
        ],
        [
          'text' => $team,
          'link' => $team_uri,
          'class' => 'team'
        ],
        [
          'text' => $board,
          'class' => 'board'
        ],
        [
          'text' => $games_count,
          'class' => 'games-count'
        ],
        [
          'text' => $points,
          'class' => 'points'
        ],
      ];
    }

    return $topscorer_table;
  }


}