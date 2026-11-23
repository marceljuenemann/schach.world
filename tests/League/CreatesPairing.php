<?php

namespace Tests\League;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\Game;
use Nsv\League\Entity\League;
use Nsv\League\Entity\Pairing;
use Nsv\League\Entity\Player;
use Nsv\League\Entity\Team;

/**
 * Creates a pairing with its own teams for tests that write to it. The tables are MyISAM,
 * so everything needs to be deleted again with deleteCreatedPairing() (see CLAUDE.md).
 */
trait CreatesPairing
{
  protected Team $home;
  protected Team $substitute;
  protected Team $away;
  protected Pairing $pairing;

  protected function createPairing(EntityManagerInterface $em, League $league, Division $division): void {
    $this->home = $this->addTeam($em, $league, 1, 'T9998');
    $this->substitute = $this->addTeam($em, $league, 2, 'T9998');
    $this->away = $this->addTeam($em, $league, 1, 'T9999');
    $em->flush();

    $this->pairing = new Pairing();
    $this->pairing->division = $division;
    $this->pairing->round = 3;
    $this->pairing->team1 = $this->home;
    $this->pairing->team2 = $this->away;
    $this->pairing->result1 = null;
    $this->pairing->result2 = null;
    $this->pairing->comment = null;
    $this->pairing->games = new ArrayCollection();
    $em->persist($this->pairing);
    $em->flush();
  }

  protected function deleteCreatedPairing(EntityManagerInterface $em): void {
    if (!isset($this->pairing)) return;
    $teams = [$this->home, $this->substitute, $this->away];
    $em->createQuery('DELETE ' . Game::class . ' g WHERE g.pairing = :pairing')->setParameter('pairing', $this->pairing)->execute();
    $em->createQuery('DELETE ' . Player::class . ' p WHERE p.team IN (:teams)')->setParameter('teams', $teams)->execute();
    $em->createQuery('DELETE ' . Pairing::class . ' p WHERE p.id = :id')->setParameter('id', $this->pairing->id)->execute();
    $em->createQuery('DELETE ' . Team::class . ' t WHERE t IN (:teams)')->setParameter('teams', $teams)->execute();
  }

  private function addTeam(EntityManagerInterface $em, League $league, int $number, string $zps): Team {
    $team = new Team();
    $team->league = $league;
    $team->name = 'Test Team';
    $team->number = $number;
    $team->zps = $zps;
    $team->venueName = null;
    $team->venueStreet = null;
    $team->venuePostCode = null;
    $team->venueCity = null;
    $team->venuePhone = null;
    $team->players = new ArrayCollection();
    $team->details = new ArrayCollection();
    $team->additionalRecipients = new ArrayCollection();
    $em->persist($team);
    $league->teams->add($team);
    return $team;
  }
}
