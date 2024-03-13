<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTeamRecipientsRequest
{
  #[Assert\NotNull]
  #[Assert\All(['constraints' => [new Assert\Email()]])]
  public array $recipients;
}
