<?php

namespace Nsv\League\Controller;

use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\Util\TextSanitizer;
use Nsv\WebApp\Core\WordPress\Auth;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract controller for a specific league, which is specified through the league slug
 * in the URL path.
 */
class AbstractLeagueController extends AbstractController {

  /**
   * The league for which the request should be executed.
   * 
   * This field is automatically set by the ControllerInterceptor if the path contains a `league` parameter. 
   */
  public ?League $league = null;

  /**
   * The division for which the request should be executed.
   * 
   * This field is automatically set by the ControllerInterceptor if the path contains a `division` parameter. 
   */
  public ?Division $division = null;

  /**
   * Info messages to show on the page.
   */
  private array $messages = [];

  /**
   * Sets up the database connection and global variables of the legacy system without
   * processing the request or outputting anything.
   */
  protected function initializeLegacySystem() {
    if (Auth::isAdmin()) {
      $_GET['debugme'] = 1;
    }

    chdir(ABSPATH . '../ligen/_inc');
    global $globals;
    $globals['basedir'] = '..';

    if (isset($this->league)) {
      $globals['league'] = $this->league;
      $globals['tid'] = $this->league->id;
      if (isset($this->division)) {
        $globals['division'] = $this->division;
        $_GET['staffel'] = $this->division->id;
      }
    }

    require_once ( "main.inc.php" );
    require_once ( "connect.inc.php" );

    // Don't send Content-Type header: https://www.saotn.org/php-56-default_charset-change-may-break-html-output/
    ini_set( 'default_charset', "" );
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

  /**
   * Create a JSON response from an API model
   */
  protected function apiResponse(mixed $model): Response {
    // TODO: Return JSON instead of phparray. Might have to do manual UTF-8 conversion first.
    Encoding::deep_utf8_encode($model);
    $response = new JsonResponse($model);
    $response->setEncodingOptions(JSON_PRETTY_PRINT);
    return $response;
  }

   /**
    * Creates a debug response from an arbitrary object.
    */
   protected function debugResponse(mixed $model): Response {
    $body = print_r($model, true);
    return new Response($body, 200, ['Content-type' => 'text/plain; charset='.Encoding::CHARSET]);
  }

  protected function addInfoMessage($message) {
    $this->messages[] = ['message' => $message, 'type' => 'info'];
  }
}
