<?php

namespace Nsv\Registration\Api\Model;

use RuntimeException;
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
   * Date from which registration is open. If not set, registration is open immediately.
   */
  #[Assert\Date]
  public ?string $registrationStart = null;

  /**
   * Date until which registration is open.
   */
  #[Assert\Date]
  public string $deadline;

  /**
   * Maximum number of players for the tournament in total.
   */
  public ?int $maxPlayers;

  /**
   * List of groups that are part of the tournament.
   */
  #[Assert\NotBlank]
  #[Assert\All(['constraints' => [new Assert\Type(GroupConfig::class)]])]
  public array $groups;

  /**
   * List of cross-group registration constraints.
   */
  #[Assert\All(['constraints' => [new Assert\Type(RegistrationConstraint::class)]])]
  public array $constraints;

  /**
   * Links to show in the frontend.
   */
  public array $links;

  /**
   * List of usernames of users allowed to manage the tournament.
   */
  #[Assert\NotBlank]
  #[Assert\All(['constraints' => [new Assert\Type('string')]])]
  public array $managers;

  /**
   * The Reply-To address for eMails.
   */
  #[Assert\NotBlank]
  #[Assert\All(['constraints' => [new Assert\Email]])]
  public array $emailReplyTo;

  /**
   * eMails addresses in CC for all eMails.
   */
  #[Assert\All(['constraints' => [new Assert\Email]])]
  public array $emailCc;

  /**
   * The terms and conditions that players need to agree to.
   */
  #[Assert\NotBlank]
  public string $termsAndConditions;

  /**
   * Additional fields that should be displayed in the registration form.
   */
  #[Assert\All(['constraints' => [new Assert\Type(AdditionalFieldConfig::class)]])]
  public array $additionalFields = [];

  function group(string $id): GroupConfig {
    foreach ($this->groups as $group) {
      if ($group->id === $id) {
        return $group;
      }
    }
    throw new RuntimeException("Group not found");
  }
}
