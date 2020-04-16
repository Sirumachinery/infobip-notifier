<?php
declare(strict_types=1);

namespace Siru\Notifier\Bridge\Infobip;

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

class InfobipTransportFactory extends AbstractTransportFactory
{

    /**
     * @inheritDoc
     * @return InfobipTransport
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        $apiKey = $this->getUser($dsn);
        $from = $dsn->getOption('from');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        if ('infobip' === $scheme) {
            return (new InfobipTransport($apiKey, $from, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
        }

        throw new UnsupportedSchemeException($dsn, 'twilio', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['infobip'];
    }

}