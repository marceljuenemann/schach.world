<?php

namespace Nsv\League\Api\Service;

class MatchDayServiceTest extends AbstractApiTest
{
  private MatchDayService $service;

  protected function setUp(): void {
    parent::setUp();
    $this->service = $this->container->get(MatchDayService::class);
  }

  // TODO: Test lastModified field manually.
  
  public function testMatchDay_round1() {
    $this->testMatchDay(1);
  }

  public function testMatchDay_round2() {
    $this->testMatchDay(2);
  }

  public function testMatchDay_round3() {
    $this->testMatchDay(3);
  }

  public function testMatchDay_round4() {
    $this->testMatchDay(4);
  }

  public function testMatchDay_round5() {
    $this->testMatchDay(5);
  }

  private function testMatchDay(int $round) {
    $ranking = function() { return ['test' => 'ranking']; };
    $model = $this->service->matchDay($this->division, $round, $ranking);
    $this->assertModel($model, __FILE__, "Round-$round");
  } 
}
