<?php

namespace Nsv\League\Api\Service;

class ScheduleServiceTest extends AbstractApiTest
{
  public function testMatchDays() {
    $matchDays = $this->container->get(ScheduleService::class)->matchDays($this->division);
    $this->assertModel($matchDays, __FILE__, __FUNCTION__);
  }
}
