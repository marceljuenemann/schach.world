<?php

namespace Nsv\League\Controller;

use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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

}
