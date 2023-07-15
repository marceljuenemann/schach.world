<?php

namespace Nsv\League\Controller;

use Nsv\League\Repository\DivisionRepository;
use Nsv\Util\TextSanitizer;
use Nsv\WebApp\Core\WordPress\Auth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Forwards requests to the legacy system for processing.
 */
class LegacyController extends AbstractLeagueController {

  function __construct(private DivisionRepository $divisionRepository) {}

  #[Route('ligen/{league}/', name: 'legacy')]
  public function legacy(Request $request): Response {
    $this->initializeLegacySystem();
    try {
      // Calculate $globals[mod], i.e. which module to call.
      global $globals;
      require_once ( "modul.inc.php" );

      // Redirect to Symfony controller if appropriate.
      if ($response = $this->checkForRedirect($globals['mod'])) {
        return $response;
      } else {
        // Existiert es überhaupt?
        $modulpfad = "$globals[basedir]/_module/$globals[mod]/$globals[mod].php";
        if ( !file_exists ( $modulpfad ) ) {
          SED_Error ( "Fehler: Das angeforderte Modul existiert nicht!", true );
        }
        ob_start();
        require_once ( $modulpfad );
      }
    } catch (\Exception $e) {
      // Report the error.
      // TODO: move this task to the logger.
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
    $response->setCharset(TextSanitizer::CHARSET);
    return $response;
  }

  private function checkForRedirect(string $module): ?Response {
    switch ($module) {
      case 'spielplan':
        $division = $this->divisionRepository->find((int) $_GET['staffel']);
        if (!$division) throw new \Exception('No division found');
        return $this->redirectToRoute('league_schedule', [
          'division' => $division->path(),
          'league' => $division->league->path
        ]);

      default:
        return null;
    }
  }
}
