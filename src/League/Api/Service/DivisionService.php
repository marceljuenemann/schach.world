<?php

namespace Nsv\League\Api\Service;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\League\Api\Request\DivisionOrderRequest;
use Nsv\League\Entity\League;
use Nsv\League\Repository\PairingRepository;

class DivisionService
{
  function __construct(
    private EntityManagerInterface $leagueEntityManager
  ) {}

  public function updateOrder(League $league, DivisionOrderRequest $request) {
    foreach ($request->divisionIds as $index => $id) {
      $league->divisionById($id)->sortId = $index;
    }
    $this->leagueEntityManager->flush();
  }
}
