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

  public function active_players_division($division) {

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

              $otto = 7;
          }
      }
      $karl = 2;


  }
}