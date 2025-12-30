<?php

namespace Nsv\Registration\Api\Model;

use Symfony\Component\Validator\Constraints as Assert;

class AdditionalFieldOption {
  /**
   * The display label for the option.
   */
  #[Assert\NotBlank]
  public string $label;

  /**
   * The value that will be stored when this option is selected.
   */
  #[Assert\NotBlank]
  public string $value;

  /**
   * Whether this option is disabled.
   */
  public bool $disabled = false;
}
