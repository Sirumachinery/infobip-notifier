<?php
declare(strict_types=1);

namespace Siru\Notifier\Bridge\Infobip;

use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class InfobipTransport extends AbstractTransport
{

    protected const HOST = 'api.infobip.com';

    private $apiKey;

    private $from;

    private $notifyUrl;

    public function __construct(string $apiKey, string $from, HttpClientInterface $client = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->apiKey = $apiKey;
        $this->from = $from;

        parent::__construct($client, $dispatcher);
    }

    public function setNotifyUrl(?string $notifuUrl) : self
    {
        $this->notifyUrl = $notifuUrl;
        return $this;
    }

    protected function doSend(MessageInterface $message): void
    {
        if (!$message instanceof SmsMessage) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, SmsMessage::class, \get_class($message)));
        }

        $endpoint = sprintf('https://%s/sms/2/text/advanced', $this->getEndpoint());
        $response = $this->client->request('POST', $endpoint, [
            'headers' => [
                'Authorization' => 'App ' . $this->apiKey,
            ],
            'json' => [
                'messages' => [
                    $this->messageToArray($message)
                ],
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            $error = $response->toArray(false);

            throw new TransportException('Unable to send the SMS: ' . $error['requestError']['serviceException']['text'], $response);
        }
    }

    private function messageToArray(SmsMessage $message) : array
    {
        $messageArray = [
            'from' => $this->from,
            'destinations' => [
                [
                    'to' => $message->getPhone()
                ]
            ],
            'text' => $message->getSubject()
        ];

        if (null !== $this->notifyUrl) {
            $messageArray['notifyUrl'] = $this->notifyUrl;
        }

        return $messageArray;
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage;
    }

    public function __toString(): string
    {
        return sprintf('infobip://%s?from=%s', $this->getEndpoint(), $this->from);
    }

}