<?php

namespace Nsv\League\Api\Service;

use Nsv\League\Core\Encoding;
use Nsv\League\Entity\Division;
use Nsv\League\Entity\League;
use Nsv\League\Repository\LeagueRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractApiTest extends KernelTestCase
{
  protected Container $container;
  protected League $league;
  protected Division $division;

  protected function setUp(): void {
    $this->container = static::getContainer();
    $this->league = $this->container->get(LeagueRepository::class)->findByPath('test');
    $this->division = $this->league->divisions[0];
  }

  protected function assertModel($model, $filename, $function) {
    // TODO: ignore IDs being different. Maybe write actual tests rather than just comparing output :D
    $path = str_replace('.php', ".$function.txt", $filename);
    $expectedPath = str_replace('/Api/Service/', '/Api/Service/expected/',  $path);
    $actualPath = str_replace('/Api/Service/', '/Api/Service/actual/',  $path);

    $expected = Encoding::utf8_decode(file_get_contents($expectedPath));
    $actual = print_r($model, true);
    file_put_contents($actualPath, Encoding::utf8_encode($actual));
    $this->assertEquals($expected, $actual);
  }
}
