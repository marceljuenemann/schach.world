<?php

namespace Nsv\League\Controller;

use Nsv\League\Core\Bridge;
use Nsv\WebApp\Core\WordPress\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeagueController extends AbstractController {

  #[Route('ligen/{leagueName}/', name: 'league')]
  public function league(string $leagueName, Bridge $symfonyBridge): Response {
    global $bridge;
    $bridge = $symfonyBridge;

    // Show all errors and notices to admins. 
    if (Auth::isAdmin()) {
      $_GET['debugme'] = 1;
    }
    
    // Hand over to legacy league manager.
    $_GET['dir'] = $leagueName;
    ob_start();
    chdir(ABSPATH . '../ligen/');
    include('index.php');    
    $body = ob_get_clean();

    $response = new Response($body);
    $response->setCharset('iso-8859-1');
    return $response;
  }
}
