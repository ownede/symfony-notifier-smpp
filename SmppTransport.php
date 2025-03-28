<?php

namespace Ksolutions\SymfonyNotifierSmpp;

use Ksolutions\SymfonyNotifierSmpp\Exception\SmppTransportException;
use PhpSmpp\Client;
use PhpSmpp\Service\Sender;
use PhpSmpp\Transport\FakeTransport;
use PhpSmpp\Transport\TransportInterface;
use SensitiveParameter;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * @author Kacper Smółkowski <kacper@ksolutions.pro>
 */
final class SmppTransport extends AbstractTransport
{
    private ?string $senderName = null;
    private ?TransportInterface $smppTransportOverride = null;

    public function __construct(
        #[SensitiveParameter] private readonly string $username,
        #[SensitiveParameter] private readonly string $password,
        private readonly string $serviceHost,
        private readonly ?int $servicePort = null,
        ?EventDispatcherInterface $dispatcher = null,
    ) {
        parent::__construct(dispatcher: $dispatcher);
    }

    public function setSenderName(?string $senderName): self
    {
        $this->senderName = $senderName;

        return $this;
    }

    /**
     * Allows setting custom transport for testing purposes.
     *
     * @see FakeTransport
     */
    public function setSmppTransportOverride(?TransportInterface $smppTransportOverride): self
    {
        $this->smppTransportOverride = $smppTransportOverride;

        return $this;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (!$message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }

        $senderName = !empty($message->getFrom()) ? $message->getFrom() : $this->senderName;
        if (empty($senderName)) {
            throw new LogicException('Sender name cannot be empty');
        }

        $senderService = $this->createSenderService();
        try {
            $smsId = $senderService->send(
                phone: $this->preparePhoneNumber($message->getPhone()),
                message: $message->getSubject(),
                from: $senderName
            );
        } catch (Throwable $e) {
            throw new SmppTransportException(
                message: sprintf('Failed to send SMS: %s', $e->getMessage()),
                previous: $e
            );
        }

        $sentMessage = new SentMessage($message, (string) $this);
        $sentMessage->setMessageId($smsId);

        return $sentMessage;
    }

    public function __toString(): string
    {
        return sprintf(
            'smpp://%s:%s@%s%s',
            $this->username,
            $this->password,
            $this->prepareEndpoint(),
            $this->senderName ? '?sender='.$this->senderName : '',
        );
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    private function createSenderService(): Sender
    {
        $sender = new Sender(
            [$this->prepareEndpoint()],
            $this->username,
            $this->password,
            Client::BIND_MODE_TRANSMITTER,
        );

        if (null !== $this->smppTransportOverride) {
            $sender->client->setTransport($this->smppTransportOverride);
        }

        return $sender;
    }

    private function prepareEndpoint(): string
    {
        return $this->servicePort ? sprintf('%s:%d', $this->serviceHost, $this->servicePort) : $this->serviceHost;
    }

    private function preparePhoneNumber(string $phoneNumber): string
    {
        return str_replace(['+'], '', $phoneNumber);
    }
}