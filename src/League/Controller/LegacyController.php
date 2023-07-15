<?php

namespace Nsv\League\Controller;

use Nsv\WebApp\Core\WordPress\Auth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Forwards requests to the legacy system for processing.
 */
class LegacyController extends AbstractLeagueController {

  #[Route('ligen/{league}/', name: 'legacy')]
  public function legacy(Request $request): Response {
    $this->initializeLegacySystem();
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
    $response = new Response($body);
    if (!isset($_GET['ausgabe']) || strtolower($_GET['ausgabe']) != 'pdf') {
      $response->setCharset(TextSanitizer::CHARSET);
    }
    return $response;
  }
}
