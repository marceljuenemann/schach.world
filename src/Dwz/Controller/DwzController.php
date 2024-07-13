<?php

namespace Nsv\Dwz\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Nsv\Dwz\DsbDatabase;
use Nsv\Dwz\Entity\Club;
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

  #[Route('players/', name: 'players')]
  public function players(
    #[MapQueryParameter] string $name,
    #[MapQueryParameter] string $preferredZps,
    #[MapQueryParameter] ?bool $active,
  ): Response {
    $entities = $this->playerRepository->searchWithPreferredZps($name, $preferredZps, $active);
    $data = array_map(function($player) {
      $data = $this->normalizer->normalize($player);
      $data['uri'] = DsbDatabase::playerRecordUri($player->fullZps());
      return $data;
    }, $entities);
    return new JsonResponse($data);
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
