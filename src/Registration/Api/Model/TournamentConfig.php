<?php

namespace Nsv\Registration\Api\Model;

use Symfony\Component\Validator\Constraints as Assert;

class TournamentConfig {
  /**
   * ID used in the URL and in the database.
   */
  #[Assert\NotBlank]
  public string $id;

  /**
   * Name of the tournament used in the headline.
   */
  #[Assert\NotBlank]
  public string $tournamentName;

  /**
   * List of groups that are part of the tournament.
   */
  #[Assert\NotBlank]
  #[Assert\All(['constraints' => [new Assert\Type(GroupConfig::class)]])]
  public array $groups;

  /**
   * List of usernames of users allowed to manage the tournament.
   */
  #[Assert\NotBlank]
  #[Assert\All(['constraints' => [new Assert\Type('string')]])]
  public array $managers;
}
