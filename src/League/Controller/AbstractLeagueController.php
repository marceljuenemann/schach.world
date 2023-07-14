<?php

namespace Nsv\League\Controller;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
  public League $league;

  /**
   * The division for which the request should be executed.
   * 
   * This field is automatically set by the ControllerInterceptor if the path contains a `division` parameter. 
   */
  public Division $division;


  protected function render(string $view, array $parameters = [], Response $response = null): Response {
    $view = '@league/' . $view;

    if ($this->league) {
      $parameters['league'] = $this->league;
      if ($this->division) {
        $parameters['division'] = $this->division;
      }
    }

    return parent::render($view, $parameters, $response);
  }
}
