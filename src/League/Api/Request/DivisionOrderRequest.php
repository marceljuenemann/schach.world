<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request payload for reordering divisions.
 */
class DivisionOrderRequest
{
  #[Assert\All(['constraints' => [new Assert\Type('integer')]])]
  public array $divisionIds;
}
