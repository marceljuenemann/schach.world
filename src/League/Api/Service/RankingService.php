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
    $teams_division = $team_repository->findByDivision($division);
    //$teams_with_pairings = $this->leagueEntityManager->getRepository(Pairing::class)->find

    //return $teams_division;
    return 'MyHouse';

  }
}