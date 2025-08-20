<?php

namespace Nsv\Registration\Api\Model;

use Symfony\Component\Validator\Constraints as Assert;

class GroupConfig {
  /**
   * ID used in the database.
   */
  #[Assert\NotBlank]
  public string $id;

  /**
   * Name of the group used in the headline.
   */
  #[Assert\NotBlank]
  public string $name;

  /**
   * Minimum DWZ constraint for this group.
   */
  public ?int $minDwz = null;

  /**
   * Maximum DWZ constraint for this group.
   */
  public ?int $maxDwz = null;

  /**
   * Minimum year of birth constraint for this group.
   */
  public ?int $minYearOfBirth = null;

  /**
   * Whether a FIDE ID is required to register.
   */
  public ?bool $requireFideId = false;

  /**
   * Maximum number of players that may register for this group.
   */
  public ?int $maxPlayers = null;
}
