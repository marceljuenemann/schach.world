<?php

namespace Nsv\League\Api\Service;
use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Entity\Team;
use Nsv\League\Entity\Pairing;
use Doctrine\Persistence\ManagerRegistry;

class RankingService {

  public function __construct (private EntityManagerInterface $leagueEntityManager) {

  }

  /**
   * A temporary method to get started
   */
  public function rankingTemp($division) {
    $team_repository = $this->leagueEntityManager->getRepository(Team::class);
    $pairing_repository = $this->leagueEntityManager->getRepository(Pairing::class);
    $teams_division = $team_repository->findByDivision($division);
    $teams_with_pairings = [];
    foreach($teams_division as $key => $team) {
      $round = 1;
      $teams_with_pairings[$key]['team'] = $team;
      $teams_with_pairings[$key]['pairings'][$round] = $pairing_repository->findByTeamOnlyPairing($team, $round);
    }

    //return $teams_division;
    return 'MyHouse';

  }
}