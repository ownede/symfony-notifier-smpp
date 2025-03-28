<?php

namespace Ksolutions\SymfonyNotifierSmpp\Tests;

use Ksolutions\SymfonyNotifierSmpp\SmppTransport;
use Ksolutions\SymfonyNotifierSmpp\SmppTransportFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Notifier\Exception\IncompleteDsnException;
use Symfony\Component\Notifier\Exception\InvalidArgumentException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\Dsn;

class SmppTransportFactoryTest extends TestCase
{
    private SmppTransportFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new SmppTransportFactory(null, null);
    }

    public function testCreateWithValidDsn()
    {
        $dsn = new Dsn('smpp://username:password@host:2775?smtp_sender=TestSender');
        $transport = $this->factory->create($dsn);

        $this->assertInstanceOf(SmppTransport::class, $transport);
        $this->assertSame('smpp://username:password@host:2775', (string)$transport);
    }

    public function testCreateThrowsUnsupportedSchemeException()
    {
        $this->expectException(UnsupportedSchemeException::class);

        $dsn = new Dsn('invalid://username:password@host:2775');
        $this->factory->create($dsn);
    }

    public function testCreateThrowsIncompleteDsnExceptionForMissingHost()
    {
        $this->expectException(InvalidArgumentException::class);

        $dsn = new Dsn('smpp://username:password@:2775');
        $this->factory->create($dsn);
    }

    public function testCreateThrowsMissingRequiredOptionExceptionForMissingUsername()
    {
        $this->expectException(IncompleteDsnException::class);

        $dsn = new Dsn('smpp://:password@host:2775');
        $this->factory->create($dsn);
    }

    public function testCreateThrowsMissingRequiredOptionExceptionForMissingPassword()
    {
        $this->expectException(IncompleteDsnException::class);

        $dsn = new Dsn('smpp://username:@host:2775');
        $this->factory->create($dsn);
    }
}