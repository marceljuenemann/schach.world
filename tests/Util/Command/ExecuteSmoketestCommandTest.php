<?php

namespace Nsv\Tests\Util\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ExecuteSmoketestCommandTest extends KernelTestCase
{

  public function testExecute()
  {
    $kernel = self::bootKernel();


    $application = new Application($kernel);
    $command = $application->find('testing:execute-smoketest');
    $commandTester = new CommandTester($command);


    $commandTester->setInputs(['Test']);
    $commandTester->execute([
      'command' => $command->getName()
      ]);
    $commandTester->assertCommandIsSuccessful();
    $output = $commandTester->getDisplay();

    $this->assertSame('holo', 'holo');
  }
}