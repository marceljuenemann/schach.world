<?php

namespace Nsv\League\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Minimal Kernel for integrating Symfony into the legacy league manager code.
 */
class Kernel {

  private $container = new ContainerBuilder();

  function __construct() {
//    $this->container->register();


  }



}
