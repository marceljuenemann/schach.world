<?php

namespace Nsv\League\Testing\Smoketests;

use Nsv\Util\Testing\Smoketest\SmoketestInterface;

class DummySmoketest implements SmoketestInterface {

    /**
     * @inheritDoc
     */
    public function urls(): array {
        // TODO: Implement urls() method.
    }

    /**
     * @inheritDoc
     */
    public function transport(): string {
        // TODO: Implement transport() method.
    }

    /**
     * @inheritDoc
     */
    public function execute() {
        // TODO: Implement execute() method.
      return 'This is only a dummy message';
    }
}