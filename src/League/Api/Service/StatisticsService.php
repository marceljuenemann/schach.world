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

  const MIN_DWZ = 700;

  public function __construct(
    private ManagerRegistry $doctrine, private Encoding $encoding, private DivisionService $divisionService
  ) {
    $this->entityManager = $this->doctrine->getManager('league');
  }

  /**
   * Return all games that have been played in a divison during
   * the season.
   */
  public function all_games_division($division) {

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

  public function active_players_division($all_games) {
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

  public function teams_with_active_players($division) {
    $team_repository = $this->doctrine->getRepository(Team::class);
    $pairing_repository = $this->doctrine->getRepository(Pairing::class);

    $teams_by_division = $team_repository->findByDivision($division);

    $teams_with_active_players = [];
    // Add pairings to teams
    foreach ($teams_by_division as &$team) {
      $pairings = $pairing_repository->findByTeam($team);
      $teams_with_active_players[$team->id]['team'] = $team;
      $teams_with_active_players[$team->id]['pairings'] = $pairings;
    }

    // Now extract the players from the pairings and add to each player the games he has played.
    foreach ($teams_with_active_players as &$team) {
      $active_players_ids = [];
      foreach ($team['pairings'] as $pairing) {
        if ($pairing->team1->id == $team['team']->id) {
          foreach ($pairing->games->getValues() as $game) {
            if (is_object($game->player1)) {
              // Make sure we add the players only once to our array
              if (!in_array($game->player1->id, $active_players_ids) && !Result::wasBye($game->result1) && !Result::wasBye($game->result2)) {
                $active_players_ids[] = $game->player1->id;
                $team['active_players'][$game->player1->id]['player'] = $game->player1;
              }
              // If the game is not a forfeit game, add it to the player's games
              if (!Result::wasBye($game->result1) && !Result::wasBye($game->result2)) {
                $team['active_players'][$game->player1->id]['games_played'][] = $game;
              }
            }
          }
        }
        if ($pairing->team2->id == $team['team']->id) {
          foreach ($pairing->games->getValues() as $game) {
            if (is_object($game->player2)) {
              // Make sure we add the players only once to our array
              // Add players to the active players only if the game has not a bye result.
              if (!in_array($game->player2->id, $active_players_ids) && !Result::wasBye($game->result1) && !Result::wasBye($game->result2)) {
                $active_players_ids[] = $game->player2->id;
                $team['active_players'][$game->player2->id]['player'] = $game->player2;
              }
              // If the game is not a forfeit game, add it to the player's games
              if (!Result::wasBye($game->result1) && !Result::wasBye($game->result2)) {
                $team['active_players'][$game->player2->id]['games_played'][] = $game;
              }
            }
          }
        }
      }
    }
    return $teams_with_active_players;
  }

  /**
   * Get all teams active in the division and add
   * active players and all players (including passive ones) as separate arrays.
   */
  public function active_teams_with_players($teams_with_active_players) {
    $team_repository = $this->doctrine->getRepository(Team::class);

    $active_teams_with_players = $teams_with_active_players;

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
      // Get it from the division, and if it is not set there get it from the league.
      $division = $team['team']->division;
      $board_count = $division->config('boardCount');
      $team_with_players = $team_repository->team_all_players($team['team']);
      $team_players = reset($team_with_players)->players->getValues();
      // Make absolutely sure the players are sorted by their numbers
      $team_players = $this->players_sorted_by_player_number($team_players);
      foreach ($team_players as $key => $team_player) {
        // reassign the player number starting from 1. The player numbers sometimes are 101 or 601 instead of 1
        $team_player->number = $key + 1;
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
  public function active_players_with_games($active_players, $all_games) {
    foreach ($active_players as $key => &$player) {
      $boards_played = [];
      $player_games_ids = [];
      if (!isset($player['games'])) {
        foreach ($all_games as $game) {
          if (is_object($game->player1) && $game->player1->id == $player['player']->id) {

            // It is probably not necessary but we check to only add a game once to
            // the player's games.
            if (!in_array($game->id, $player_games_ids)) {
              $player_games_ids[] = $game->id;
              $player['games'][] = $game;
              if (!empty($game->board && !in_array($game->board, $boards_played))) {
                $boards_played[] = $game->board;
              }
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
              if (!empty($game->board && !in_array($game->board, $boards_played))) {
                $boards_played[] = $game->board;
              }
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
          if (count($boards_played) > 1) {
            $min_board = min($boards_played);
            $max_board = max($boards_played);
            if ($min_board != $max_board) {
              $player['boards_played'] = $min_board . '-' . $max_board;
            }
          } else {
            $player['boards_played'] = reset($boards_played);
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
  public function teams_dwz_calculation($active_teams_with_players) {
    $active_teams_with_dwz = $active_teams_with_players;
    $dwz_data = [];

    foreach ($active_teams_with_dwz as $key => &$team) {
      // Start counting the total games for each team seperately
      $team_total_games = 0;
      // Add up all DWZ numbers from the active players
      $dwz_data[$key]['active_dwz_sum'] = (int)0;
      foreach ($team['active_players'] as $player) {
        $dwz = $player['player']->dwz;
        $player_id = $player['player']->id();

        $games_played = count($player['games_played']);


        // Add up the total games played in order to divide the sum by that number later.
        $team_total_games += $games_played;

        if (empty($dwz)) {
          $dwz_data[$key]['active_dwz_sum'] += self::MIN_DWZ * $games_played;
        } else {
          $dwz_data[$key]['active_dwz_sum'] += $player['player']->dwz * $games_played;
        }
      }
      // calculate the active DWZ average
      // Get the numbers of games each player has played

      $dwz_active_average = $dwz_data[$key]['active_dwz_sum'] / $team_total_games;
      $team['active_dwz_average'] = round($dwz_active_average);

      // Add up all DWZ numbers from all players (including passive)
      $dwz_data[$key]['all_dwz_sum'] = (int)0;
      foreach ($team['all_players'] as $player) {
        $dwz = $player->dwz;
        if (empty($dwz)) {
          $dwz_data[$key]['all_dwz_sum'] += self::MIN_DWZ;
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
          $dwz_data[$key]['top_dwz_sum'] += self::MIN_DWZ;
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
        $birthyear = $player['player']->birth;
        $date = new \DateTime();
        $timezone = new \DateTimeZone('Europe/Berlin');
        $date->setTimezone($timezone);
        $current_year = $date->format('Y');

        $games_played = count($player['games_played']);

        if (!empty($birthyear)) {
          $dwz_data[$key]['active_age_sum'] += ($current_year - $birthyear) * $games_played;
          $aged_players_count += 1;
        }
      }
      // calculate the active age average
      // It could be that we have no age for any of the players.
      if (!empty($aged_players_count)) {
        $age_active_average = $dwz_data[$key]['active_age_sum'] / $team_total_games;
        $team['active_age_average'] = round($age_active_average);
      }
    }
    // Sort by age to find the team with the highest age average
    uasort($active_teams_with_dwz, function ($a, $b) {
      return [$b['active_age_average']] <=> [$a['active_age_average']];
    });


    foreach ($active_teams_with_dwz as &$team) {
      if ($team['team']->id == reset($active_teams_with_dwz)['team']->id) {
        $team['age_rank'] = 'top';
      } else {
        $team['age_rank'] = 'normal';
      }
    }
    // Sort by all_dwz to the team with the highest top_dwz average
    uasort($active_teams_with_dwz, function ($a, $b) {
      return [$b['top_x_dwz_average']] <=> [$a['top_x_dwz_average']];
    });

    // Mark the team with the highest all players DWZ average
    // so we can format that bold in the table
    foreach ($active_teams_with_dwz as &$team) {
      if ($team['team']->id == reset($active_teams_with_dwz)['team']->id) {
        $team['top_dwz_rank'] = 'top';
      } else {
        $team['top_dwz_rank'] = 'normal';
      }
    }

    // Sort by all_dwz to the team with the highest all_dwz average
    uasort($active_teams_with_dwz, function ($a, $b) {
      return [$b['all_dwz_average']] <=> [$a['all_dwz_average']];
    });

    // Mark the team with the highest all players DWZ average
    // so we can format that bold in the table
    foreach ($active_teams_with_dwz as &$team) {
      if ($team['team']->id == reset($active_teams_with_dwz)['team']->id) {
        $team['all_dwz_rank'] = 'top';
      } else {
        $team['all_dwz_rank'] = 'normal';
      }
    }

    // Sort the teams by the highest DWZ average
    uasort($active_teams_with_dwz, function ($a, $b) {
      return [$b['active_dwz_average']] <=> [$a['active_dwz_average']];
    });

    // Mark the team with the highest DWZ average
    // so we can format that bold in the table
    // We sort by this number last because this is the way
    // we display the table.
    foreach ($active_teams_with_dwz as &$team) {
      if ($team['team']->id == reset($active_teams_with_dwz)['team']->id) {
        $team['dwz_rank'] = 'top';
      } else {
        $team['dwz_rank'] = 'normal';
      }
    }

    return $active_teams_with_dwz;
  }

  /**
   * Get the active teams and collect the pairings with each team.
   */
  public function active_teams_with_parings($division) {
    $all_games = $this->all_games_division($division);
    $teams_with_pairings = [];
    $active_teams_ids = [];

    foreach ($all_games as $game) {
      // Make sure we add the teams only once to our array
      if (!in_array($game->pairing->team1->id, $active_teams_ids)) {
        $active_teams_ids[] = $game->pairing->team1->id;
        $teams_with_pairings[$game->pairing->team1->id]['team'] = $game->pairing->team1;
        $teams_with_pairings[$game->pairing->team1->id]['pairings'][$game->pairing->id] = $game->pairing;
      } elseif (in_array($game->pairing->team1->id, $active_teams_ids)) {
        $teams_with_pairings[$game->pairing->team1->id]['pairings'][$game->pairing->id] = $game->pairing;
      }

      // Now add the teams from the team2 reference
      if (!in_array($game->pairing->team2->id, $active_teams_ids)) {
        $active_teams_ids[] = $game->pairing->team2->id;
        $teams_with_pairings[$game->pairing->team2->id]['team'] = $game->pairing->team2;
        $teams_with_pairings[$game->pairing->team2->id]['pairings'][$game->pairing->id] = $game->pairing;
      } elseif (in_array($game->pairing->team2->id, $active_teams_ids)) {
        $teams_with_pairings[$game->pairing->team2->id]['pairings'][$game->pairing->id] = $game->pairing;
      }
    }

    return $teams_with_pairings;
  }


  /**
   * Calculate the data for the team game score "Spiel-Statistik"
   */
  public function team_game_score_data($active_teams_with_parings) {
    $teams_game_scores = [];


    foreach ($active_teams_with_parings as $team_id => $team) {
      $teams_game_scores[$team_id]['name'] = $team['team']->nameWithNumber();
      $teams_game_scores[$team_id]['uri'] = $team['team']->uri();
      $teams_game_scores[$team_id]['game_count'] = (int)0;
      $teams_game_scores[$team_id]['game_count_played'] = (int)0;
      $teams_game_scores[$team_id]['forfeit_wins'] = (int)0;
      $teams_game_scores[$team_id]['forfeit_losses'] = (int)0;
      $teams_game_scores[$team_id]['wins'] = 0;
      $teams_game_scores[$team_id]['draws'] = 0;
      $teams_game_scores[$team_id]['losses'] = 0;
      $teams_game_scores[$team_id]['white_count'] = (int)0;
      $teams_game_scores[$team_id]['white_points'] = 0;
      $teams_game_scores[$team_id]['white_score'] = 0;
      $teams_game_scores[$team_id]['black_count'] = (int)0;
      $teams_game_scores[$team_id]['black_points'] = 0;
      $teams_game_scores[$team_id]['black_score'] = 0;
      foreach ($team['pairings'] as $pairing) {
        $teams_game_scores[$team_id]['game_count'] += count($pairing->games->getValues());
        // Get scores for games results

        if ($pairing->team1->id == $team_id) {
          $result_select = 'result1';
        } elseif ($pairing->team2->id == $team_id) {
          $result_select = 'result2';
        }

        if (isset($result_select)) {

          foreach ($pairing->games->getValues() as $game_key => $game) {
            $result = $this->encoding->utf8_encode($game->$result_select);

            switch ($result) {
              case '+':
                $teams_game_scores[$team_id]['forfeit_wins'] += 1;
                break;
              case '-':
                $teams_game_scores[$team_id]['forfeit_losses'] += 1;
                break;
              case 1:
                $teams_game_scores[$team_id]['wins'] += 1;
                $teams_game_scores[$team_id]['game_count_played'] += 1;
                break;
              case Result::UNICODE_DRAW:
                $teams_game_scores[$team_id]['draws'] += 1;
                $teams_game_scores[$team_id]['game_count_played'] += 1;
                $result = 0.5;
                break;
              case 0:
                $teams_game_scores[$team_id]['losses'] += 1;
                $teams_game_scores[$team_id]['game_count_played'] += 1;
                break;
            }
            // Collect how many games have been actually played with white
            // and black and add up the score for each
            if ($pairing->team1->id == $team_id) {
              // If the team is the home team and thus team1, it plays on board 2,4,6 etc with white.
              // Translated to array keys those are the odd ones like 1,3,5 etc.
              // Those are the numbers not divisible by two and not zero
              if ($game_key != 0 && $game_key % 2 != 0) {
                if ($result != '+' && $result != '-') {
                  $teams_game_scores[$team_id]['white_count'] += 1;
                  $teams_game_scores[$team_id]['white_points'] += $result;
                }
              }
              // Now reverse the logic and cound the black games for the home team
              if ($game_key == 0 || $game_key % 2 == 0) {
                if ($result != '+' && $result != '-') {
                  $teams_game_scores[$team_id]['black_count'] += 1;
                  $teams_game_scores[$team_id]['black_points'] += $result;
                }
              }
            }
            // Collect white games for the away team
            if ($pairing->team2->id == $team_id) {
              if ($game_key == 0 || $game_key % 2 == 0) {
                if ($result != '+' && $result != '-') {
                  $teams_game_scores[$team_id]['white_count'] += 1;
                  $teams_game_scores[$team_id]['white_points'] += $result;
                }
              }
              // black games
              if ($game_key != 0 && $game_key % 2 != 0) {
                if ($result != '+' && $result != '-') {
                  $teams_game_scores[$team_id]['black_count'] += 1;
                  $teams_game_scores[$team_id]['black_points'] += $result;
                }
              }

            }
          }
        }
      }
      // Convert the scores for the actually played games to percentages
      $game_count_played = $teams_game_scores[$team_id]['game_count_played'];

      $win_percentage = 100 * ($teams_game_scores[$team_id]['wins'] / $game_count_played);
      $teams_game_scores[$team_id]['wins'] = $win_percentage;

      $draw_percentage = 100 * ($teams_game_scores[$team_id]['draws'] / $game_count_played);
      $teams_game_scores[$team_id]['draws'] = $draw_percentage;

      $loss_percentage = 100 * ($teams_game_scores[$team_id]['losses'] / $game_count_played);
      $teams_game_scores[$team_id]['losses'] = $loss_percentage;

      // Convert the white and black points to percentages
      if ($teams_game_scores[$team_id]['white_count'] == 0) {
        $white_percentage = 0;
      } else {
        $white_percentage = 100 * ($teams_game_scores[$team_id]['white_points'] / $teams_game_scores[$team_id]['white_count']);
      }
      $teams_game_scores[$team_id]['white_score'] = round($white_percentage);
      unset($teams_game_scores[$team_id]['white_points'], $teams_game_scores[$team_id]['white_count']);

      if ($teams_game_scores[$team_id]['black_count'] == 0) {
        $black_percentage = 0;
      } else {
        $black_percentage = 100 * ($teams_game_scores[$team_id]['black_points'] / $teams_game_scores[$team_id]['black_count']);
      }
      $teams_game_scores[$team_id]['black_score'] = round($black_percentage);
      unset($teams_game_scores[$team_id]['black_points'], $teams_game_scores[$team_id]['black_count']);

//      $combined_white_black_percentage = round($white_percentage) + round($black_percentage);

//      $teams_game_scores[$team_id]['combined_score'] = $combined_white_black_percentage;

    }
    // Sort the teams by their combined score
//    uasort($teams_game_scores, function ($a, $b) {
//      return [$b['combined_score']] <=> [$a['combined_score']];
//    });

    return $teams_game_scores;
  }

  /**
   * Sort the players by their registration (board) number.
   * This number sometimes does not start with 1 but 101 or 601
   */
  public function players_sorted_by_player_number($players) {
    uasort($players, function ($a, $b) {
      return [$a->number] <=> [$b->number];
    });
    return $players;
  }


  /**
   * Sort the players by points first and by played games after that.
   * Who has played less games will be sorted further up.
   */
  public function players_sorted_by_points_and_games($active_players_with_games) {
    uasort($active_players_with_games, function ($a, $b) {
      return [$b['points'], count($a['games'])] <=> [$a['points'], count($b['games'])];
    });
    return $active_players_with_games;
  }

  /**
   * Sort the players by draws
   */
  public function players_sorted_by_draws($active_players_with_games) {
    uasort($active_players_with_games, function ($a, $b) {
      return [$b['draws']] <=> [$a['draws']];
    });
    return $active_players_with_games;
  }


  /**
   * Create the table array for DWZ statistics that
   * is sent to the template in the controller.
   */
  public function create_dwz_statistics_table($division) {
    $all_games = $this->all_games_division($division);
    $active_players = $this->active_players_division($all_games);
    // Sorry for the very similar naming of the methods teams_with_active_players and active_teams_with_players
    // but I had no better idea.
    $teams_with_active_players = $this->teams_with_active_players($division);
    $active_teams_with_players = $this->active_teams_with_players($teams_with_active_players);
    $dwz_calculation = $this->teams_dwz_calculation($active_teams_with_players);

    // Get the board count
    $first_team = reset($dwz_calculation);
    $division = $first_team['team']->division;
    $board_count = $division->config('boardCount');

    // We also return part of statistics text, so the dwz_table is only part of the returned data

    $dwz_data = [];

    $dwz_table = [];
    $dwz_text = '';

    $dwz_table['header'] = [
      [
        'text' => 'Mannschaft',
        'class' => 'team'
      ],
      [
        'text' => 'Eingesetzte',
        'class' => 'active',
        'title' => $this->encoding->utf8_decode('DWZ Durchschnitt der Spieler, die tatsächlich gespielt haben. Spieler ohne DWZ werden als DWZ 700 gewertet.')
      ],
      [
        'text' => 'Top ' . $board_count,
        'class' => 'top',
        'title' => $this->encoding->utf8_decode('Durchschnittliche DWZ der Stammspieler. Spieler ohne DWZ werden als DWZ 700 gewertet.')
      ],
      [
        'text' => 'Alle Spieler',
        'class' => 'all',
        'title' => $this->encoding->utf8_decode('Durchschnittliche DWZ von allen gemeldeten Spielern. Spieler ohne DWZ werden als DWZ 700 gewertet.')
      ],
      [
        'text' => $this->encoding->utf8_decode('Alter Ø'),
        'class' => 'age',
        'title' => $this->encoding->utf8_decode('Durchschnittliches Alter der Spieler, die tatsächlich gespielt haben.')
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
      $team_name = $team['team']->nameWithNumber();
      $team_uri = $team['team']->uri();
      $dwz_active = $team['active_dwz_average'];
      $dwz_top = $team['top_x_dwz_average'];
      $dwz_all = $team['all_dwz_average'];
      $age = $team['active_age_average'];
      if ($team['dwz_rank'] == 'top') {
        $active_dwz_classes = 'active-dwz fw-bold ';
      } else {
        $active_dwz_classes = 'active-dwz';
      }
      if ($team['top_dwz_rank'] == 'top') {
        $top_dwz_classes = 'top-dwz fw-bold';
      } else {
        $top_dwz_classes = 'top-dwz';
      }
      if ($team['all_dwz_rank'] == 'top') {
        $all_dwz_classes = 'all-dwz fw-bold';
      } else {
        $all_dwz_classes = 'all-dwz';
      }
      if ($team['age_rank'] == 'top') {
        $age_classes = 'age fw-bold';
      } else {
        $age_classes = 'age';
      }

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
          'class' => $active_dwz_classes,
        ],
        [
          'text' => $dwz_top,
          'class' => $top_dwz_classes
        ],
        [
          'text' => $dwz_all,
          'class' => $all_dwz_classes
        ],
        [
          'text' => $age,
          'class' => $age_classes
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

    $dwz_text_values = [
      'dwz_active_average' => $average_values['dwz_active'],
      'age_average' => $average_values['age'],
    ];

    // Add an extra row to the table with the average values for all teams.
    $dwz_table['body'][] = [
      [
        'text' => 'Durchschnitt:',
        'class' => 'average-active fw-bold'
      ],
      [
        'text' => $average_values['dwz_active'],
        'class' => 'average-active fw-bold'
      ],
      [
        'text' => $average_values['dwz_top'],
        'class' => 'average-top fw-bold'
      ],
      [
        'text' => $average_values['dwz_all'],
        'class' => 'average-all fw-bold'
      ],
      [
        'text' => $average_values['age'],
        'class' => 'average-age fw-bold'
      ],
    ];

    $dwz_data['table'] = $dwz_table;
    $dwz_data['text_values'] = $dwz_text_values;

    return $dwz_data;

  }

  /**
   * Create the table array for topscorers that
   * is sent to the template in the controller.
   */
  public function create_topscorer_table($division) {
    $all_games = $this->all_games_division($division);
    $active_players = $this->active_players_division($all_games);
    $active_players_with_games = $this->active_players_with_games($active_players, $all_games);
    $players_with_games_by_points_and_games = $this->players_sorted_by_points_and_games($active_players_with_games);
    $players_with_games_by_draws = $this->players_sorted_by_draws($active_players_with_games);
    $top_ten_drawers = array_slice($players_with_games_by_draws, 0, 10, true);
    $top_ten_scorers = array_slice($players_with_games_by_points_and_games, 0, 10, true);

    $topscorer_data = [];
    $topscorer_table = [];
    $topscorer_text = '';

    $topscorer_table['header'] = [
      ['text' => 'Name', 'class' => 'name'],
      ['text' => 'DWZ', 'class' => 'rating-national'],
      ['text' => 'Mannschaft', 'class' => 'team'],
      ['text' => 'Brett', 'class' => 'board'],
      ['text' => 'Partien', 'class' => 'games'],
      ['text' => 'Punkte', 'class' => 'points']
    ];

    // find the topscorer(s) and the draw king(s)
    $first_player = reset($top_ten_scorers);
    $highest_points_score = $first_player['points'];
    // The lowest game score of the players with the most points
    $lowest_game_score = count($first_player['games']);
    $top_scorers = [];


    foreach ($top_ten_scorers as $key => $player) {
      $player_name = $player['player']->name();
      $player_uri = $player['player']->uri();
      $dwz = $player['player']->dwz ?? '';
      $team = $player['player']->team->name . ' ' . $player['player']->team->number;
      $team_uri = $player['player']->team->uri();
      $board = $player['boards_played'] ?? '';
      $games_count = count($player['games']);
      $points = $player['points'];

      // Collect the top scorers
      if ($player['points'] == $highest_points_score && $games_count == $lowest_game_score) {
        $top_scorers[] = $player;
      }

      $topscorer_table['body'][] = [
        [
          'text' => $player_name,
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
          'class' => 'points fw-bold'
        ],
      ];
    }


    $text_top_scorers = [];
    foreach ($top_scorers as $key => $scorer) {
      $text_top_scorers[$key]['player_name'] = $scorer['player']->name();
      $text_top_scorers[$key]['player_uri'] = $scorer['player']->uri();
      $text_top_scorers[$key]['team_name'] = $scorer['player']->team->nameWithNumber();
      $text_top_scorers[$key]['team_uri'] = $scorer['player']->team->uri();
    }

    // Collect the draw kings
    $first_drawer = reset($top_ten_drawers);
    $highest_draw_score = $first_drawer['draws'];
    $draw_kings = [];

    foreach ($top_ten_drawers as $drawer) {
      if ($drawer['draws'] == $highest_draw_score) {
        $draw_kings[] = $drawer;
      }
    }
    $text_draw_kings = [];
    foreach ($draw_kings as $key => $drawer) {
      $text_draw_kings[$key]['player_name'] = $drawer['player']->name();
      $text_draw_kings[$key]['player_uri'] = $drawer['player']->uri();
      $text_draw_kings[$key]['team_name'] = $drawer['player']->team->nameWithNumber();
      $text_draw_kings[$key]['team_uri'] = $drawer['player']->team->uri();
    }

    $topscorer_data['text_values']['text_top_scorers'] = $text_top_scorers;
    $topscorer_data['text_values']['text_draw_kings'] = $text_draw_kings;
    $topscorer_data['text_values']['highest_point_score'] = $highest_points_score;
    $topscorer_data['text_values']['lowest_game_score'] = $lowest_game_score;
    $topscorer_data['text_values']['highest_draw_score'] = $highest_draw_score;


    $topscorer_data['table'] = $topscorer_table;

    return $topscorer_data;
  }

  /**
   * Create the table array for the team game score that
   * is sent to the template in the controller.
   */
  public function create_team_game_score_table($division) {
    $active_teams_with_parings = $this->active_teams_with_parings($division);
    $team_game_score_data = $this->team_game_score_data($active_teams_with_parings);

    $team_game_score_table = [];

    $team_game_score_table['header'] = [
      [
        'text' => 'Mannschaft',
        'class' => 'team'
      ],
      [
        'text' => '&sum;',
        'class' => 'game-all-count border-left-bold',
        'title' => $this->encoding->utf8_decode('Wie viele Partien hat die Mannschaft bislang gespielt?')
      ],
      [
        'text' => '+',
        'class' => 'forfeit-wins',
        'title' => $this->encoding->utf8_decode('Kampflose Siege')
      ],
      [
        'text' => '-',
        'class' => 'forfeit-losses border-right-bold',
        'title' => $this->encoding->utf8_decode('Kampflose Niederlagen')
      ],
      [
        'text' => '1',
        'class' => 'wins',
        'title' => $this->encoding->utf8_decode('Siege aus den wirklich gespielten Partien')
      ],
      [
        'text' => $this->encoding->utf8_decode(Result::UNICODE_DRAW),
        'class' => 'draws',
        'title' => $this->encoding->utf8_decode('Remis')
      ],
      [
        'text' => '0',
        'class' => 'losses border-right-bold',
        'title' => $this->encoding->utf8_decode('Niederlagen')
      ],
      [
        'text' => 'W',
        'class' => 'white-score',
        'title' => $this->encoding->utf8_decode('Score mit Weiß')
      ],
      [
        'text' => 'S',
        'class' => 'black-score',
        'title' => $this->encoding->utf8_decode('Score mit Schwarz')
      ],
    ];

    // Set initial values for the last table row that displays the average scores
    $sum_game_count = 0;
    $sum_game_count_played = 0;
    $sum_forfeit_wins = 0;
    $sum_forfeit_losses = 0;
    $sum_wins = 0;
    $sum_draws = 0;
    $sum_losses = 0;
    $sum_white_score = 0;
    $sum_black_score = 0;

    foreach ($team_game_score_data as $key => $team) {
      $team_game_score_table['body'][] = [
        [
          'text' => $team['name'],
          'link' => $team['uri'],
          'class' => 'name'
        ],
        [
          'text' => $team['game_count_played'],
          'class' => 'game-all-count border-left-bold',
        ],
        [
          'text' => $team['forfeit_wins'],
          'class' => 'forfeit-wins'
        ],
        [
          'text' => $team['forfeit_losses'],
          'class' => 'forfeit-losses border-right-bold',
        ],
        [
          'text' => round($team['wins']) . '%',
          'class' => 'wins',
        ],
        [
          'text' => round($team['draws']) . '%',
          'class' => 'draws',
        ],
        [
          'text' => round($team['losses']) . '%',
          'class' => 'losses border-right-bold',
        ],
        [
          'text' => round($team['white_score']) . '%',
          'class' => 'white-score',
        ],
        [
          'text' => round($team['black_score']) . '%',
          'class' => 'black-score',
        ],
      ];

      $sum_game_count += $team['game_count'];
      $sum_game_count_played += $team['game_count_played'];
      $sum_forfeit_wins += $team['forfeit_wins'];
      $sum_forfeit_losses += $team['forfeit_losses'];
      $sum_wins += $team['wins'];
      $sum_draws += $team['draws'];
      $sum_losses += $team['losses'];
      $sum_white_score += $team['white_score'];
      $sum_black_score += $team['black_score'];

    }

    //The total sum of games must be halved, since always two players
    // of different teams are playing in one game.
    // Sum with forfeits
    $sum_game_count = $sum_game_count / 2;

    // Sum of actually played games
    $sum_game_count_played = $sum_game_count_played / 2;


    // Calculate the average values
    $team_count = count($active_teams_with_parings);
    $average_wins = $sum_wins / $team_count;
    $average_draws = $sum_draws / $team_count;
    $average_losses = $sum_losses / $team_count;
    $average_white_score = $sum_white_score / $team_count;
    $average_black_score = $sum_black_score / $team_count;

    // Add the average values to the table

    $team_game_score_table['body'][] = [
      [
        'text' => 'Summe:',
        'class' => 'name fw-bold'
      ],
      [
        'text' => $sum_game_count,
        'class' => 'game-all-count border-left-bold fw-bold',
        'title' => $this->encoding->utf8_decode('All games including forfeits. The sum is only half of the above, since always two players of different teams play one game.'),
      ],
      [
        'text' => $sum_forfeit_wins,
        'class' => 'forfeit-wins fw-bold'
      ],
      [
        'text' => $sum_forfeit_losses,
        'class' => 'forfeit-losses border-right-bold fw-bold',
      ],
      [
        'text' => round($average_wins) . '%',
        'class' => 'wins fw-bold',
      ],
      [
        'text' => round($average_draws) . '%',
        'class' => 'draws fw-bold',
      ],
      [
        'text' => round($average_losses) . '%',
        'class' => 'losses border-right-bold fw-bold',
      ],
      [
        'text' => round($average_white_score) . '%',
        'class' => 'white-score fw-bold',
      ],
      [
        'text' => round($average_black_score) . '%',
        'class' => 'black-score fw-bold',
      ],
    ];

    // Create the team game score text.
    $forfeit_percentage = 100 * ($sum_forfeit_losses / $sum_game_count);

    $team_game_score_values = [
      'sum_forfeit_losses' => $sum_forfeit_losses,
      'forfeit_percentage' => round($forfeit_percentage),
      'sum_game_count_played' => $sum_game_count_played,
      'average_draws' => round($average_draws),
      'average_white_score' => round($average_white_score),
      'average_black_score' => round($average_black_score),
    ];

    $team_game_score_data['table'] = $team_game_score_table;
    $team_game_score_data['text_values'] = $team_game_score_values;
    return $team_game_score_data;
  }

}