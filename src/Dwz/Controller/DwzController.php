<?php

namespace Nsv\Dwz\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Entity\Player;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Controller for the DWZ API.
 */
#[Route('/dwz/api/', name: 'dwz_')]
class DwzController extends AbstractController {

  function __construct(private NormalizerInterface $normalizer) {}

  #[Route('players/', name: 'players')]
  public function players(EntityManagerInterface $mainEntityManager): Response {
    $player = $mainEntityManager->getRepository(Player::class)->findOneByName('Jünemann,Marcel');
    $data = $this->normalizer->normalize($player);
    return new JsonResponse($data);
  }
}
