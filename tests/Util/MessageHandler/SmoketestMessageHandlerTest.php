<?php

namespace Util\MessageHandler;

use Monolog\Handler\TestHandler;
use Nsv\Util\MessageHandler\SmoketestMessageHandler;
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use PHPUnit\Framework\TestCase;

class SmoketestMessageHandlerTest extends TestCase {
  private EncoderInterface $encoder;
  private Logger $logger;
  private SmoketestMessageHandler $smoketestMessageHandler;

  protected function setUp(): void {
    $this->encoder = $this->createMock(EncoderInterface::class);
    $this->logger = $this->createMock(Logger::class);
    $this->smoketestMessageHandler = new SmoketestMessageHandler($this->logger, $this->encoder);
  }

  /**
   * @dataProvider checkUrlProvider
   */
  public function testCheckUrl($url, $statusCode) {
    $response = $this->smoketestMessageHandler->checkUrl($url);
    $this->assertSame($statusCode, $response['status_code']);
  }
  private function checkUrlProvider(): \Generator {
    yield ['Status 200 ok'] => ['https://httpstat.us/200', 200];
    yield ['Status 404 Not Found'] => ['https://httpstat.us/404', 404];
    yield ['Status 500 Internal Error'] => ['https://httpstat.us/500', 500];
  }
}