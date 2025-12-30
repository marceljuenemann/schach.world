<?php

namespace Nsv\League\Controller;

use Nsv\League\Core\Encoding;
use Nsv\League\Core\LeagueAuthState;
use Nsv\League\Core\LegacySystem;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Abstract controller for a specific league, which is specified through the league slug
 * in the URL path.
 */
class AbstractLeagueController extends AbstractController {

  function __construct(
    protected League $league,
    protected LeagueAuthState $auth,
    private LegacySystem $legacySystem
  ) {}

  /**
   * The division for which the request should be executed.
   *
   * Remove from AbstractLeagueController once no longer passed to
   * the legacy system.
   */
  protected ?Division $division = null;

  /**
   * Info messages to show on the page.
   */
  private array $messages = [];

  /**
   * Sets up the database connection and global variables of the legacy system without
   * processing the request or outputting anything.
   */
  protected function initializeLegacySystem() {
    $this->legacySystem->initialize();
    $this->legacySystem->league = $this->league;
    $this->legacySystem->division = $this->division;
    $this->initializeLegacySession();
  }

  /**
   * Sets the global $admin variable if the user is logged in.
   */
  private function initializeLegacySession() {
    if (!$this->auth->isDivisionManager()) return;

    global $admin;
    $division = $this->auth->isLeagueManager() ? null : $this->auth->managedDivision();
    $user = $division ? $division->manager : $this->league->manager;
    $admin = [
      'usertype' => $division ? 's' : 't',
      'userid' => $user->id,
      'username' => $user->name,
      'usermail' => $user->mail,
      'staffel' => $division ? $division->id : 0,
      'pageid' => isset($_GET['admin']) ? substr($_GET['admin'], 0, strpos($_GET['admin'], '-')) : null,
      'session' => ''
    ];
  }

  /**
   * Renders a twig template using the legacy system only for the UI headers and footers.
   */
  protected function renderWithLegacySystem(string $view, array $parameters = []): Response {
    $this->initializeLegacySystem();
    require_once ( "turnier.inc.php" );
    return $this->render($view, $parameters);
  }

  /**
   * Renders a league twig template, while providing some common variables.
   */
  protected function render(string $view, array $parameters = [], Response $response = null): Response {
    $view = '@league/' . $view;

    $parameters['auth'] = $this->auth;
    $parameters['messages'] = $this->messages;
    if ($this->league) {
      $parameters['league'] = $this->league;
      if ($this->division) {
        $parameters['division'] = $this->division;
      }
    }

    $response = parent::render($view, $parameters, $response);
    $response->setCharset(Encoding::CHARSET);
    return $response;
  }

  protected function apiResponse(mixed $model = new \stdClass): Response {
    Encoding::deep_utf8_encode($model);
    $response = new JsonResponse($model);
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
    return $response;
  }

  protected function debugResponse(mixed $model): Response {
    return new Response(print_r($model, true), 200, ['Content-type' => 'text/plain; charset='.Encoding::CHARSET]);
  }

  protected function addInfoMessage($message, $type = 'info') {
    $this->messages[] = ['message' => $message, 'type' => $type];
  }

  /**
   * Renders an error page with all the usual UI and sidebar.
   */
  public function errorResponse(Throwable $exception): Response|null {
    if ($exception instanceof AccessDeniedHttpException) {
      $this->addInfoMessage("Fehler 403: Zugriff nicht erlaubt oder abgelaufen ({$exception->getMessage()})", 'danger');
      return $this->renderWithLegacySystem('error.html.twig');
    }
    if ($exception instanceof NotFoundHttpException) {
      $this->addInfoMessage('Fehler 404: Seite nicht gefunden', 'danger');
      return $this->renderWithLegacySystem('error.html.twig');
    }
    return null;
  }
}
