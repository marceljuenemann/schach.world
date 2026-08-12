<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class ReorderPlayersRequest
{
  #[Assert\NotNull]
  #[Assert\Count(min: 1)]
  #[Assert\All(['constraints' => [new Assert\Type('integer')]])]
  public array $playerIds;
}
