<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Testing\DatabaseTestCase;

class DivisionTest extends DatabaseTestCase
{
  public function testMatchDayUri() {
    $this->assertEquals("/ligen/{$this->league->path}/?staffel={$this->division1->id}&r=", $this->division1->matchDayUri());
    $this->assertEquals("/ligen/{$this->league->path}/?staffel={$this->division1->id}&r=2", $this->division1->matchDayUri(2));
  }
}
