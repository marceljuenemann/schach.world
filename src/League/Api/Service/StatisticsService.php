<?php

namespace Nsv\League\Api\Service;

use Doctrine\Persistence\ManagerRegistry;
use Nsv\League\Entity\Pairing;

class StatisticsService {
    public function __construct(
        private ManagerRegistry $doctrine
    ) {
        $this->entityManager = $this->doctrine->getManager('league');
    }

    public function team_all_games($division) {
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
        foreach($data as $key => $pairing) {
            $table['body'][$key][]['team1'] = ['text' => $pairing->team1->name, 'class' => 'team-name'];
            $table['body'][$key][]['team2'] = ['text' => $pairing->team2->name, 'class' => 'team-name'];
            $result1 = $pairing->result1;
            $result2 = $pairing->result2;
            $table['body'][$key][]['result'] = [
                'text' => $pairing->result1 . ' : ' . $pairing->result2,
                'class' => 'result'
            ];
            $games = $pairing->games->getValues();
            foreach($games as $key => $game) {
                $player1_name = $game->player1->firstName . ' ' . $game->player1->lastName;
                $player2_name = $game->player2->firstName . ' ' . $game->player2->lastName;
                $table['body'][$key][]['game1'] = [

                ];

            }

        }

        return $data;
    }
}