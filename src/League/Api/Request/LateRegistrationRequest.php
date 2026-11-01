<?php

namespace Nsv\League\Api\Request;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A player that gets registered while saving a pairing.
 */
class LateRegistrationRequest
{
  /**
   * The number of the team to register the player for, which is either the
   * team playing the pairing or one of its substitute teams.
   */
  #[Assert\NotNull]
  public int $teamNumber;

  #[Assert\NotNull]
  #[Assert\Valid]
  public CreateOrUpdatePlayerRequest $player;
}
