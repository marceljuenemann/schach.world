<?php

namespace Nsv\League\Api\Service;

class ScheduleServiceTest extends AbstractApiTest
{
  private ScheduleService $service;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(ScheduleService::class);
  }

  public function testClosestDate() {
    $dates = ['2020-01-01', '2020-01-04'];
    $this->assertEquals('2020-01-04', $this->service->closestDate($dates, '2020-01-03'));
  }

  public function testClosestDate_exactDate() {
    $dates = ['2020-01-01', '2020-01-04', '2020-01-06'];
    $this->assertEquals('2020-01-04', $this->service->closestDate($dates, '2020-01-04'));
  }

  public function testClosestDate_noDates() {
    $this->assertNull($this->service->closestDate([], '2020-01-04'));
  }

  public function testMatchesByDate() {
    $matchDays = $this->service->matchesByDate($this->league, '2025-01-01');
    $this->assertModel($matchDays, __FILE__, __FUNCTION__);
  }

  public function testMatchDays() {
    $matchDays = $this->service->matchDays($this->division);
    $this->assertModel($matchDays, __FILE__, __FUNCTION__);
  }

}
