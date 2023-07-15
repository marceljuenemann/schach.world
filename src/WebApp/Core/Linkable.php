<?php

namespace Nsv\WebApp\Core;

/**
 * Linkable classes can easily be linked to using the nsv_link twig function.
 * 
 * TODO: Move somewhere more sensible?
 */
interface Linkable {

  function linkUri();

  function linkTitle();

}
