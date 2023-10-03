<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateTeamRecipientsRequest
{
  #[Assert\NotBlank]
  #[Assert\All(['constraints' => [new Assert\Email()]])]
  public array $recipients;
}
