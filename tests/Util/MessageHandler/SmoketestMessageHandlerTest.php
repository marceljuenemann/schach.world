<?php

namespace Util\MessageHandler;

use Monolog\Handler\TestHandler;
use Nsv\Util\MessageHandler\SmoketestMessageHandler;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SmoketestMessageHandlerTest extends KernelTestCase {
  private TestHandler $testHandler;
  private Logger $logger;
  private SmoketestMessageHandler $smoketestMessageHandler;
  private

  protected function setUp(): void {
    $this->testHandler = new TestHandler();
    $this->logger->pushHandler($this->testHandler);
    $this->smoketestMessageHandler = new SmoketestMessageHandler($this->logger,);

  }
}