<?php

namespace Nsv\League\Api\Service;

use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Pairing;
use Nsv\League\Core\Result;

class StatisticsService
{
  public function __construct(
    private ManagerRegistry $doctrine, private Encoding $encoding
  )
  {
    $this->entityManager = $this->doctrine->getManager('league');
  }

  public function team_all_games($division)
  {
    $pairing_repository = $this->doctrine->getRepository(Pairing::class);
    $data = $pairing_repository->findAllGamesDivision($division);

    $table = [];
    $table['header'] = [
      ['text' => 'Team 1', 'class' => 'team'],
      ['text' => 'Team 2', 'class' => 'team'],
      ['text' => 'Ergebnis', 'class' => 'result'],
      ['text' => 'Game 1', 'class' => 'team'],
      ['text' => 'Game 2', 'class' => 'team'],
      ['text' => 'Game 3', 'class' => 'team'],
      ['text' => 'Game 4', 'class' => 'team']
    ];

    // Collect all data for our table
    foreach ($data as $key => $pairing) {
      $table['body'][$key][] = ['text' => $pairing->team1->name . ' ' . $pairing->team1->number, 'class' => 'team-name team1'];
      $table['body'][$key][] = ['text' => $pairing->team2->name . ' ' . $pairing->team2->number, 'class' => 'team-name team2'];
      $table['body'][$key][] = [
        'text' => $pairing->result1 . ' : ' . $pairing->result2,
        'class' => 'result'
      ];
      $games = $pairing->games->getValues();
      foreach ($games as $key2 => $game) {
        $player_1_first_name = $game->player1->firstName ?? '';
        $player_1_last_name = $game->player1->lastName ?? '';
        $player_2_first_name = $game->player2->firstName ?? '';
        $player_2_last_name = $game->player2->lastName ?? '';

        $player1_name = $player_1_first_name . ' ' . $player_1_last_name;
        $player2_name = $player_2_first_name . ' ' . $player_2_last_name;
        if ($key2 == 0) {
          $table['body'][$key][] = [
            'text' => $player1_name . ' - ' . $player2_name,
            'class' => 'game1'
          ];
        }
        if ($key2 == 1) {
          $table['body'][$key][] = [
            'text' => $player1_name . ' - ' . $player2_name,
            'class' => 'game2'
          ];
        }
        if ($key2 == 2) {
          $table['body'][$key][] = [
            'text' => $player1_name . ' - ' . $player2_name,
            'class' => 'game3'
          ];
        }
        if ($key2 == 3) {
          $table['body'][$key][] = [
            'text' => $player1_name . ' - ' . $player2_name,
            'class' => 'game4'
          ];
        }
      }
    }
    return $table;
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
    $games = $all_games;
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