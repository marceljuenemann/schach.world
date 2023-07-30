<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Entity\League;
use Nsv\League\Entity\Player;
use Nsv\League\Entity\Team;
use PHPUnit\Framework\TestCase;

class TeamTest extends TestCase
{
  public function testIsSubstituteTeam_sameZps() {
    $team1 = new Team();
    $team1->zps ='70156';
    $team1->number = 2;
    $team1->league = new League();

    $team2 = new Team();
    $team2->zps ='70156';

    $team2->number = 3;
    $team1->league->configSubstituteTeams = 0;
    $this->assertFalse($team1->isSubstituteTeam($team2));

    $team1->league->configSubstituteTeams = 1;
    $this->assertTrue($team1->isSubstituteTeam($team2));

    $team2->number = 4;
    $this->assertFalse($team1->isSubstituteTeam($team2));

    $team1->league->configSubstituteTeams = 99;
    $this->assertTrue($team1->isSubstituteTeam($team2));
  }

  public function testIsSubstituteTeam_differentZps() {
    $team1 = new Team();
    $team1->zps = '70156';
    $team1->name = "SK Lehrte";
    $team1->number = 2;
    $team1->league = new League();
    $team1->league->configSubstituteTeams = 1;

    $team2 = new Team();
    $team2->zps = '70101';
    $team2->name = "SK Lehrte";
    $team2->number = 3;

    $this->assertFalse($team1->isSubstituteTeam($team2));
  }

  public function testIsSubstituteTeam_noZps_sameName() {
    $team1 = new Team();
    $team1->name = "SK Lehrte";
    $team1->zps = null;
    $team1->number = 2;
    $team1->league = new League();
    $team1->league->configSubstituteTeams = 1;

    $team2 = new Team();
    $team2->zps = null;
    $team2->name = "SK Lehrte";
    $team2->number = 3;

    $this->assertTrue($team1->isSubstituteTeam($team2));
  }

  public function testIsSubstituteTeam_noZps_differentName() {
    $team1 = new Team();
    $team1->name = "SK Lehrte";
    $team1->zps = null;
    $team1->number = 2;
    $team1->league = new League();
    $team1->league->configSubstituteTeams = 1;

    $team2 = new Team();
    $team2->zps = null;
    $team2->name = "HSK";
    $team2->number = 3;

    $this->assertFalse($team1->isSubstituteTeam($team2));
  }

  public function testIsSubstituteTeam_differentGroups() {
    $team1 = new Team();
    $team1->zps ='70156';
    $team1->number = 2;
    $team1->league = new League();
    $team1->league->configSubstituteTeams = 1;

    $team2 = new Team();
    $team2->zps ='70156';
    $team2->number = 3;
    $team2->group = 'U12';

    $this->assertFalse($team1->isSubstituteTeam($team2));
  }

  public function testIsSubstituteTeam_sameGroup() {
    $team1 = new Team();
    $team1->zps ='70156';
    $team1->number = 2;
    $team1->group = 'U12';
    $team1->league = new League();
    $team1->league->configSubstituteTeams = 1;

    $team2 = new Team();
    $team2->zps ='70156';
    $team2->number = 3;
    $team2->group = 'U12';

    $this->assertTrue($team1->isSubstituteTeam($team2));
  }

}
