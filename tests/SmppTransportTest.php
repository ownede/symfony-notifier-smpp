<?php

namespace Ksolutions\SymfonyNotifierSmpp\Tests;

use Ksolutions\SymfonyNotifierSmpp\SmppTransport;
use PhpSmpp\Service\Sender;
use PhpSmpp\Transport\FakeTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;

class SmppTransportTest extends TestCase
{
    private string $username = 'test_user';
    private string $password = 'test_password';
    private string $serviceHost = 'localhost';
    private ?int $servicePort = 1234;
    private SmppTransport $transport;

    protected function setUp(): void
    {
        $this->transport = new SmppTransport(
            $this->username,
            $this->password,
            $this->serviceHost,
            $this->servicePort
        );
        $this->transport->setSmppTransportOverride(new FakeTransport());
    }

    public function testToStringWithoutPort(): void
    {
        $this->transport = new SmppTransport(
            $this->username,
            $this->password,
            $this->serviceHost
        );

        $this->assertSame(
            'smpp://test_user:test_password@localhost',
            (string) $this->transport
        );
    }

    public function testToStringWithSenderName(): void
    {
        $this->transport->setSenderName('TestSender');
        $this->assertSame(
            'smpp://test_user:test_password@localhost:1234?sender=TestSender',
            (string) $this->transport
        );
    }

    public function testToStringWithoutSenderName(): void
    {
        $this->assertSame(
            'smpp://test_user:test_password@localhost:1234',
            (string) $this->transport
        );
    }

    public function testSupportsSmsMessageReturnsTrue(): void
    {
        $smsMessage = new SmsMessage('1234567890', 'Test message');
        $this->assertTrue($this->transport->supports($smsMessage));
    }

    public function testSupportsNonSmsMessageReturnsFalse(): void
    {
        $nonSmsMessage = $this->createMock(MessageInterface::class);
        $this->assertFalse($this->transport->supports($nonSmsMessage));
    }

    public function testDoSendThrowsUnsupportedMessageTypeExceptionForInvalidMessage(): void
    {
        $this->expectException(UnsupportedMessageTypeException::class);

        $invalidMessage = $this->createMock(MessageInterface::class);
        $this->transport->send($invalidMessage);
    }

    public function testDoSendThrowsLogicExceptionWhenSenderIsEmpty(): void
    {
        $this->expectException(LogicException::class);

        $smsMessage = new SmsMessage('1234567890', 'Test message');
        $this->transport->send($smsMessage);
    }

    public function testDoSendSuccessfullySendsMessage(): void
    {
        $smsMessage = new SmsMessage('1234567890', 'Test message');
        $this->transport->setSenderName('TestSender');

        $mockSenderService = $this->createMock(Sender::class);
        $mockSenderService->method('send')->willReturn('SMS-ID-123');

        $sentMessage = $this->transport->send($smsMessage);

        $this->assertInstanceOf(SentMessage::class, $sentMessage);
        $this->assertNotEmpty($sentMessage->getMessageId());
    }
}