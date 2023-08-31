<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Api\Request\CreateDivisionRequest;
use Nsv\League\Api\Request\DivisionOrderRequest;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Entity\LegacyUser;

class DivisionService
{
  function __construct(
    private EntityManagerInterface $leagueEntityManager
  ) {}

  public function createDivision(League $league, CreateDivisionRequest $request) {
    $user = new LegacyUser();
    $user->name = $request->managerName;
    $user->mail = $request->managerMail;
    $user->phone = $request->managerPhone;
    $user->phone2 = $request->managerPhone2;
    $user->password = md5($request->managerPassword);
    $this->leagueEntityManager->persist($user);

    $division = new Division();
    $division->league = $league;
    $division->name = $request->name;
    $division->manager = $user;
    $division->sortId = count($league->divisions);
    $this->leagueEntityManager->persist($division);
    $this->leagueEntityManager->flush();
  }

  public function updateOrder(League $league, DivisionOrderRequest $request) {
    foreach ($request->divisionIds as $index => $id) {
      $league->divisionById($id)->sortId = $index;
    }
    $this->leagueEntityManager->flush();
  }
}
