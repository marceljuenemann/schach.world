<?php

namespace Nsv\Registration\Api\Model;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Configures a registration constraint across multiple groups.
 */
class RegistrationConstraint {
  /**
   * The group IDs for which this constraint applies.
   */
  #[Assert\NotBlank]
  #[Assert\All(['constraints' => [new Assert\Type('string')]])]
  public array $groups;

  /**
   * Maximum number of players that may register for the specified groups.
   */
  public ?int $maxPlayers = null;
}
