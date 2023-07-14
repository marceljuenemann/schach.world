<?php

namespace Nsv\League\Controller;

use Nsv\League\Core\Bridge;
use Nsv\WebApp\Core\WordPress\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeagueController extends AbstractController {

  /**
   * Main entry point for the League Manager. Exact action is determined by query parameters.
   */
  #[Route('ligen/{leagueName}/', name: 'league')]
  public function league(
        string $leagueName, 
        Bridge $symfonyBridge,
        Request $request
      ): Response {
    $this->initializeLegacySystem($symfonyBridge);
    $response = $this->invokeLegacySystem($leagueName, $request);
    $response->setCharset('iso-8859-1');
    return $response;
  }

  /**
   * Sets up database connection and global variables of the legacy system without
   * processing the request or outputting anything.
   */
  private function initializeLegacySystem(Bridge $symfonyBridge) {
    if (Auth::isAdmin()) {
      $_GET['debugme'] = 1;
    }

    global $globals, $bridge;
    chdir(ABSPATH . '../ligen/_inc');
    $globals['basedir'] = '..';
    $bridge = $symfonyBridge;

    require_once ( "main.inc.php" );
    require_once ( "connect.inc.php" );

    // Don't send Content-Type header: https://www.saotn.org/php-56-default_charset-change-may-break-html-output/
    ini_set( 'default_charset', "" );
  }

  /**
   * Invokes the legacy system for processing the request and generating output.
   */
  private function invokeLegacySystem(string $leagueName, Request $request): Response {
    $_GET['dir'] = $leagueName;
    ob_start();
    try {
      // Aufzurufendes Modul in $globals [mod] schreiben
      global $globals;
      require_once ( "modul.inc.php" );

      // TODO: redirect to Symfony controller if appropriate.

      // Existiert es überhaupt?
      $modulpfad = "$globals[basedir]/_module/$globals[mod]/$globals[mod].php";
      if ( !file_exists ( $modulpfad ) ) {
        SED_Error ( "Fehler: Das angeforderte Modul existiert nicht!", true );
      }
      require_once ( $modulpfad );
    } catch (\Exception $e) {
      // Report the error.
      if (!Auth::isAdmin()) {
        global $globals;
        @wp_mail($globals['webmaster_mail'], 'LeagueController Exception', $request->getUri() . "\n\n".$e);
      }

      // The legacy script often outputs HTML before fully processing the request.
      if (function_exists('SED_GUIclose')) {
        SED_Error('Leider ist ein Fehler aufgetreten :(');
        if (Auth::isAdmin()) {
          echo "<pre style='text-wrap: wrap'>$e</pre>";
        }
      } else {
        ob_end_clean();
        throw $e;
      }
    }
    // Output the footer.
    if ( function_exists ( "SED_GUIclose" ) ) {
      SED_GUIclose ();
    }
    $body = ob_get_clean();
    return new Response($body);
  }
}
