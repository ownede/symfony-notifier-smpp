<?php

namespace Ksolutions\SymfonyNotifierSmpp;

use Symfony\Component\Notifier\Exception\IncompleteDsnException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * @author Kacper Smółkowski <kacper@ksolutions.pro>
 */
final class SmppTransportFactory extends AbstractTransportFactory
{
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();

        if ('smpp' !== $scheme) {
            throw new UnsupportedSchemeException($dsn, 'smpp', $this->getSupportedSchemes());
        }

        $username = $this->getUser($dsn);
        $password = $this->getPassword($dsn);
        $host = $this->getHost($dsn);
        $sender = $dsn->getOption('sender');

        return (new SmppTransport(
            username: $username,
            password: $password,
            serviceHost: $host,
            servicePort: $dsn->getPort(),
            dispatcher: $this->dispatcher,
        ))->setSenderName($sender);
    }

    protected function getSupportedSchemes(): array
    {
        return ['smpp'];
    }

    protected function getHost(Dsn $dsn): string
    {
        return $dsn->getHost() ?? throw new IncompleteDsnException('Host is not set.', $dsn->getOriginalDsn());
    }
}