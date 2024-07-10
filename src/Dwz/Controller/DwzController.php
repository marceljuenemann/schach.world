<?php

namespace Nsv\Dwz\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Controller for the DWZ API.
 */
#[Route('/dwz/api/', name: 'dwz_')]
class DwzController extends AbstractController {

  function __construct(private SerializerInterface $serializer) {}

  #[Route('players/', name: 'players')]
  public function players(EntityManagerInterface $mainEntityManager): Response {
    $player = $mainEntityManager->getRepository(Player::class)->findByName('Jünemann,Marcel');
    $json = $this->serializer->serialize($player, 'json');
    return new Response($json);
  }
}
