<?php

namespace Nsv\League\Controller;

use Nsv\League\Core\Bridge;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeagueController extends AbstractController {

  #[Route('ligen/{leagueName}/', name: 'league')]
  public function league(string $leagueName, Bridge $symfonyBridge): Response {
    global $bridge;
    $bridge = $symfonyBridge;

    // Hand over to legacy league manager.
    chdir(ABSPATH . '../ligen/');
    $_GET['dir'] = $leagueName;
    ob_start();
    include('index.php');    
    $body = ob_get_clean();

    $response = new Response($body);
    $response->setCharset('iso-8859-1');
    return $response;
  }
}
