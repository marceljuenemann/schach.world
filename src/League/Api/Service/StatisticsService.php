<?php

namespace Nsv\League\Api\Service;

use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Pairing;

class StatisticsService
{
  public function __construct(
    private ManagerRegistry $doctrine
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
  public function all_games_division($division) {

      $pairing_repository = $this->doctrine->getRepository(Pairing::class);
      $data = $pairing_repository->findAllGamesDivision($division);

      $all_games = [];
      $all_games_ids = [];

      foreach ($data as $key => $pairing) {
          $games = $pairing->games->getValues();
          foreach ($games as $key2 => $game) {
              if(!in_array($game->id, $all_games_ids)) {
                  $all_games_ids[] = $game->id;
                  $all_games[] = $game;
              }
          }
      }
      return $all_games;
  }

  public function active_players_division($all_games) {
    $games = $all_games;
    $active_players = [];
    $active_players_ids = [];
    foreach($all_games as $game) {
      if(is_object($game->player1)) {
        // Make sure we add the players only once to our array
        if(!in_array($game->player1->id, $active_players_ids)) {
          $active_players_ids[] = $game->player1->id;
          $active_players[]['player'] = $game->player1;
        }
      }
      if(is_object($game->player2)) {
        if(!in_array($game->player2->id, $active_players_ids)) {
          // Make sure we add the players only once to our array
          $active_players_ids[] = $game->player2->id;
          $active_players[]['player'] = $game->player1;
        }
      }
    }
   return $active_players;
  }

  /**
   * Return all active players with their played games as a subarray
   * for each player.
   */
  public function active_players_with_games($active_players, $all_games) {
    $players_with_games = [];
    foreach($active_players as $key => &$player){
      $player_games_ids = [];
      if(!isset($player['games'])) {
        foreach($all_games as $game) {
          if(is_object($game->player1) && $game->player1->id  == $player['player']->id) {
            // It is probably not necessary but we check to only add a game once to
            // the player's games.
            if(!in_array($game->id, $player_games_ids)) {
              $player_games_ids[] = $game->id;
              $player['games'][] = $game;
            }
          }
          if(is_object($game->player2) && $game->player2->id == $player['player']->id) {
            if(!in_array($game->id, $player_games_ids)) {
              $player_games_ids[] = $game->id;
              $player['games'][] = $game;
            }
          }
        }
      }

    }
    $susi = 'still';
  }

}