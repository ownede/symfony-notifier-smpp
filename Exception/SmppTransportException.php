<?php

namespace Ksolutions\SymfonyNotifierSmpp\Exception;

use Symfony\Component\Notifier\Exception\RuntimeException;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Throwable;

final class SmppTransportException extends RuntimeException implements TransportExceptionInterface
{
    public function __construct(
        string $message,
        ?Throwable $previous = null,
        private readonly string $debug = ''
    ) {
        parent::__construct(
            message: $message,
            previous: $previous,
        );
    }

    public function getDebug(): string
    {
        return $this->debug;
    }
}