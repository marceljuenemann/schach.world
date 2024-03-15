<?php

namespace Nsv\League\Entity;

use Doctrine\ORM\Mapping as ORM;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A league runs for one season and consists of one or more divisions.
 */
#[ORM\Entity(repositoryClass: LeagueRepository::class)]
#[ORM\Table(name: 'turniere')]
class League
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private int $id;

  #[ORM\Column(length: 40)]
  private string $name;

  #[ORM\Column(name: 'directory', length: 20)]
  private string $path;

  /**
   * For German chess federations, this should be the ZPS code prefix of the organisation.
   * 
   * Note that some organisation-specific rules are hardcoded in Regulation.php
   */
  #[ORM\Column(name: 'organisation', length: 15)]
  private string $organisation;

  #[ORM\Column(name: 'template')]
  private string $theme;

  /**
   * The year in which the league started.
   */
  #[ORM\Column(name: 'startjahr')]
  private ?int $year;

  /**
   * The legacy user of the league manager.
   * 
   * TODO: Use WordPress user system.
   * TODO: Allow multiple users to manage a league.
   */
  #[ORM\OneToOne(targetEntity: LegacyUser::class)]
  #[ORM\JoinColumn(name: 'leiter', referencedColumnName: 'id')]
  private $manager;

  /**
   * Number of rounds.
   */
  #[ORM\Column(name: 'runden')]
  private int $configRounds = 9;

  /**
   * Number of boards per match.
   */
  #[ORM\Column(name: 'brettzahl')]
  private int $configBoardCount = 8;

  /**
   * Number of subsequent teams whose players may be used as substitute players.
   */
  #[ORM\Column(name: 'spielErsatzmannschaft')]
  private int $configSubstituteTeams = 0;

  /**
   * Whether to send eMails with result links to team leaders, so that they
   * can enter results themselves.
   */
  #[ORM\Column(name: 'sysEingabelinks')]
  private bool $configSendResultLinkMails = false;

  /**
   * Number of teams that will be promoted to a higher league.
   */
  #[ORM\Column(name: 'spielAufsteiger')]
  private int $configTeamsPromoted = 0;

  /**
   * Number of teams that will be relegated.
   */
  #[ORM\Column(name: 'spielAbsteiger')]
  private int $configTeamsDemoted = 0;

  /**
   * Number of teams that face a relegation battle for a higher league.
   */
  #[ORM\Column(name: 'spielAufsteigerRelegation')]
  private int $configTeamsMaybePromoted = 0;

  /**
   * Number of teams that face a relegation battle for a lower league.
   */
  #[ORM\Column(name: 'spielAbsteigerRelegation')]
  private int $configTeamsMaybeDemoted = 0;

  /**
   * Whether to show player numbers in the UI.
   */
  #[ORM\Column(name: 'showPassNr')]
  private bool $configPlayerNumbers = true;

  /**
   * Whether to prefix all player numbers with the team number, e.g.
   * 302 instead of 2 for the second player in the third team.
   */
  #[ORM\Column(name: 'spielDreistelligeNr')]
  private bool $configPlayerNumbersWithTeamNumber = false;

  /**
   * Whether to allow late registrations by team managers.
   */
  #[ORM\Column(name: 'spielNachmeldungen')]
  private bool $configLateRegistrationEnabled = true;

  /**
   * Whether to show late registrations on the match day page.
   */
  #[ORM\Column(name: 'showNachmeldungen')]
  private bool $configShowLateRegistrations = true;

  /**
   * Whether to show a preview of the next match day.
   */
  #[ORM\Column(name: 'showSpieltagvorschau')]
  private bool $configShowNextMatchDay = true;

  #[ORM\Column(name: 'showTabelle')]
  private bool $configShowRanking = true;

  #[ORM\Column(name: 'anmAktiv')]
  private bool $registrationEnabled = false;

  #[ORM\Column(name: 'anmGeburt')]
  private int $registrationMinYearOfBirth = 1900;

  #[ORM\Column(name: 'anmGeschlecht', length: 1)]
  private string $registrationSex;

  #[ORM\Column(name: 'anmVerband', length: 3)]
  private string $registrationOrganisationZps;

  /**
   * Whether the league manager should receive a confirmation mail for
   * each registration.
   */
  #[ORM\Column(name: 'anmTLMail')]
  private bool $registrationConfirmationMail = true;

  /**
   * Additional team details to ask for during registration.
   * TODO: document the format
   */
  #[ORM\Column(name: 'anmZusatzfelder')]
  private string $registrationTeamDetails = '';

  /**
   * A public message to be shown.
   */
  #[ORM\Column(name: 'infomeldung')]
  private string $announcement = '';

  #[ORM\OneToMany(targetEntity: Division::class, mappedBy: 'league')]   
  #[ORM\OrderBy(["sortId" => "ASC", "id" => "ASC"])]
  private $divisions;

  #[ORM\OneToMany(targetEntity: Team::class, mappedBy: 'league')]
  #[ORM\OrderBy(["name" => "ASC", "number" => "ASC"])]
  private $teams;

  #[ORM\OneToMany(targetEntity: Date::class, mappedBy: 'league')]
  #[ORM\OrderBy(["division" => "DESC"])]
  private $dates;

  public function uri() {
    return "/ligen/{$this->path}/";
  }
  
  /**
   * @throws NotFoundHttpException
   */
  public function divisionByPath(string $path) {
    foreach ($this->divisions as $division) {
      if ($path === $division->path()) {
        return $division;
      }
    }
    throw new NotFoundHttpException('Division not found');
  }

  /**
   * @throws NotFoundHttpException
   */
  public function divisionById(int $id): Division {
    foreach ($this->divisions as $division) {
      if ($id === $division->id) {
        return $division;
      }
    }
    throw new NotFoundHttpException('Division not found');
  }

  /**
   * @throws NotFoundHttpException
   */
  public function teamById($id): Team {
    // We always fetch all teams anyways, so this is the most efficient way.
    foreach ($this->teams as $team) {
      if ($team->id === $id) {
        return $team;
      }
    }
    throw new NotFoundHttpException('Team not found');
  }

  public function __call($property, $args) {
    return $this->$property;
  }

  public function __get($property) {
    return $this->$property;
  }

  public function __set($property, $value) {
    $this->$property = $value;
  }
}
