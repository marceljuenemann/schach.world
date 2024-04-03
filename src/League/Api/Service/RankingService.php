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
  public function teamsWithPairings($division, $round) {
    $team_repository = $this->leagueEntityManager->getRepository(Team::class);
    $pairing_repository = $this->leagueEntityManager->getRepository(Pairing::class);
    $teams_division = $team_repository->findByDivision($division);
    $teams_with_pairings = [];
    foreach($teams_division as $team) {
      $teams_with_pairings[$team->id]['team'] = $team;
      $pairings = $pairing_repository->findByTeamOnlyPairing($team, $round);
      $teams_with_pairings[$team->id]['pairings'] = $pairings;
      $teams_with_pairings[$team->id]['team_points'] = $this->addTeamPoints($team, $pairings);
      $teams_with_pairings[$team->id]['board_points'] = $this->addBoardPoints($team, $pairings);
    }
  // Sort the teams by team_points and after that by board_points.
    uasort($teams_with_pairings, function ($a, $b) {
      return [$b['team_points'], $b['board_points']] <=> [$a['team_points'], $a['board_points']];
    });

    //return $teams_division;
    return 'MyHouse';
  }

  /**
   * Calculate the team points from the team and its pairings.
   */
  public function addTeamPoints($team, array $pairings) {
    $team_points = (int) 0;
    foreach($pairings as $pairing) {
      if($pairing->team1->id == $team->id) {
        $team_points += $this->teamPointsFromResult($pairing->result1, $pairing->result2);
      }
      if($pairing->team2->id == $team->id) {
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
    foreach($pairings as $pairing) {
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
    if($result1 > $result2) {
      $team_points = 2;
    }
    if($result1 < $result2) {
      $team_points = 0;
    }
    // This is for the case the pairing was not played at all
    if(empty($result1) && empty($result1)) {
      $team_points = 0;
    }
    if($result1 == $result2 && !empty($result1)) {
      $team_points = 1;
    }
    return $team_points;
  }

}