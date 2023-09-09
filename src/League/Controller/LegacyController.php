<?php

namespace Nsv\League\Controller;

use Doctrine\ORM\EntityNotFoundException;
use Nsv\League\Core\Encoding;
use Nsv\League\Core\LeagueAuthProvider;
use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Entity\League;
use Nsv\League\Repository\DivisionRepository;
use Nsv\League\Repository\PlayerRepository;
use Nsv\League\Repository\TeamRepository;
use Nsv\WebApp\Core\NsvJs;
use Nsv\WebApp\Core\WordPress\Auth as WordPressAuth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Forwards requests to the legacy system for processing.
 */
class LegacyController extends AbstractLeagueController {

  function __construct(
    private DivisionRepository $divisionRepository,
    private PlayerRepository $playerRepository,
    private TeamRepository $teamRepository,
    private NsvJs $nsvJs,
    League $league,
    LeagueAuthState $auth
  ) {
    parent::__construct($league, $auth);
  }

  #[Route('ligen/{league}/', name: 'legacy')]
  public function legacy(Request $request, LeagueAuthProvider $authProvider): Response {
    $this->initializeLegacySystem();
    try {
      // Calculate $globals[mod], i.e. which module to call.
      global $globals;
      require_once ( "modul.inc.php" );

      // Redirect to Symfony controller if appropriate.
      if ($response = $this->checkForRedirect($globals['mod'])) {
        return $response;
      } else if ($globals['mod'] === 'staffelleiter') {
        // Handle legacy admin system.
        if ($_GET['admin'] === 'login') {
          $authProvider->legacyLogin($_POST['benutzer'], $_POST['passwort']);
          return $this->redirect($this->league->uri() . "?admin=desktop--");
        } else if (str_starts_with($_GET['admin'], 'logout-')) {
          $authProvider->legacyLogout();
          return $this->redirect($this->league->uri());
        } else {
          $this->legacyAdminSystem();
        }
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
      if (!($e instanceof NotFoundHttpException) && !WordPressAuth::isAdmin()) {
        global $globals;
        @wp_mail($globals['webmaster_mail'], 'LeagueController Exception', $request->getUri() . "\n\n".$e);
      }

      // The legacy script often outputs HTML before fully processing the request.
      if (function_exists('SED_GUIclose')) {
        SED_Error('Leider ist ein Fehler aufgetreten :(');
        if (WordPressAuth::isAdmin()) {
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
    $response->setCharset(Encoding::CHARSET);
    return $response;
  }

  private function checkForRedirect(string $module): ?Response {
    try {
      switch ($module) {
        case 'startseite':
          return $this->forward(
            MainController::class . '::overview',
            ['league' => $this->league->path],
            isset($_GET['date']) ? ['date' => $_GET['date']] : []
          );

        case 'spielplan':
          $division = $this->divisionRepository->find($_GET['staffel']);
          return $this->redirectToRoute('league_schedule', [
            'division' => $division->path(),
            'league' => $division->league->path
          ]);

        case 'mannschaft':
          $team = $this->teamRepository->find($_GET['mannschaft']);
          return $this->redirectToRoute('league_team', [
            'teamId' => $team->id,
            'league' => $team->league->path
          ]);

        case 'spieler':
          $player = $this->playerRepository->find($_GET['spieler']);
          return $this->redirectToRoute('league_player', [
            'playerId' => $player->id,
            'league' => $player->team->league->path
          ]);

        default:
          return null;
      }
    } catch (EntityNotFoundException $e) {
      throw new NotFoundHttpException($e->getMessage());
    } 
  }

  private function legacyAdminSystem() {
    $this->auth->requireDivisionManager();

    ob_start();
    require_once('login.inc.php');
    if (isset($_GET['type'])) {
      require_once ( "$globals[basedir]/_module/ajax/ajax.php");
    } else {
      require_once ( "gui.inc.php" );
      echo $admin['toptxt'];
      require_once ( $globals['basedir'] . "/_module/staffelleiter/" . $admin['pageid'] . ".php" );
    }

    // Enable integrating React components into the legacy admin system.
    echo "<script src='{$this->nsvJs->scriptUrl()}'></script>";
  }
}
