<?php

namespace Nsv\Registration\Api\Model;

use Symfony\Component\Validator\Constraints as Assert;

class AdditionalFieldConfig {
  /**
   * The type of the field.
   */
  #[Assert\NotBlank]
  #[Assert\Choice(['text', 'multiline', 'int', 'select'])]
  public string $type;

  /**
   * Unique identifier for the field.
   */
  #[Assert\NotBlank]
  #[Assert\Regex('/^[a-zA-Z0-9_]+$/')]
  public string $id;

  /**
   * Display label for the field.
   */
  #[Assert\NotBlank]
  public string $label;

  /**
   * Whether the field is required.
   */
  public bool $required = false;

  /**
   * Options for select fields.
   */
  #[Assert\All(['constraints' => [new Assert\Type(FieldOptionConfig::class)]])]
  public array $options = [];
}
