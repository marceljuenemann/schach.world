<?php

namespace Nsv\Dwz\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for the DWZ API.
 */
#[Route('/dwz/api/', name: 'dwz_')]
class DwzController extends AbstractController {

  #[Route('players/', name: 'players')]
  public function players(EntityManagerInterface $mainEntityManager): Response {
    $player = $mainEntityManager->getRepository(Player::class)->find('Jünemann,Marcel');
    return new Response($player->name);
  }
}
