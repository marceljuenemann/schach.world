<?php

namespace Nsv\Dwz\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\Entity\Club;
use Nsv\Dwz\Entity\Player;
use Nsv\Dwz\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Controller for the DWZ API.
 */
#[Route('/dwz/api/', name: 'dwz_')]
class DwzController extends AbstractController {

  function __construct(
    private EntityManagerInterface $mainEntityManager,
    private PlayerRepository $playerRepository,
    private NormalizerInterface $normalizer
  ) {}

  // TODO: Add prefered ZPS
  #[Route('players/', name: 'players')]
  public function players(
    #[MapQueryParameter] string $name,
    #[MapQueryParameter] string $zps,
    #[MapQueryParameter] ?bool $active
  ): Response {
/*
    return array_map(function($player) {
      $player['link'] = NsvDsbSpielerLink($player['ZPS'], $player['Mgl_Nr']);
      return $player;
    }, $results);
*/

    $entities = $this->playerRepository->search($name, $zps, $active);

    return new JsonResponse($this->normalizer->normalize($entities));    
  }

  #[Route('clubs/', name: 'clubs')]
  public function clubs(#[MapQueryParameter] string $name, #[MapQueryParameter] string $zps): Response {
    $entities = $this->mainEntityManager->getRepository(Club::class)
      ->createQueryBuilder('club')
      ->where('club.name LIKE :name')
      ->andWhere('club.zps LIKE :zps')
      ->orderBy('club.name', 'ASC')
      ->setMaxResults(10)
      ->setParameter('name', "%$name%")
      ->setParameter('zps', "$zps%")
      ->getQuery()
      ->getResult();
    return new JsonResponse($this->normalizer->normalize($entities));
  }
}
