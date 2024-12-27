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
   * Maximum DWZ constraint for this group.
   */
  public ?int $maxDwz;

  /**
   * Minimum year of birth constraint for this group.
   */
  public ?int $minYearOfBirth;
}
