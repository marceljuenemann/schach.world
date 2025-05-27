<?php

namespace Nsv\Util\MessageHandler;

use Nsv\Util\Message\SmoketestMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SmoketestMessageHandler {
  public function __construct(private iterable $smoketestInstances) {}

  public function __invoke(SmoketestMessage $message): void {
    $className = $message->getClassName();
    foreach ($this->smoketestInstances as $smoketestInstance) {
      $name = get_class($smoketestInstance);
      $result = $smoketestInstance->execute();
      $papa = 'rolling stone';
    }

    $willi = 'Schuhe';
  }
}
