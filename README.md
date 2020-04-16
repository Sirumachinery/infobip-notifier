# Infobip Notifier

Provides Infobip integration for Symfony Notifier.

## Requirements

- PHP 7.2
- Symfony Notifier and HttpClient
- API key and base url from Infobip

## Installation

```shell script
$ composer require sirumobile/infobip-notifier
```

Add correct DSN with your Infobip API key and API base url to ENV. Then configure notifier and
add InfobipTransportFactory to your services.

```dotenv
# .env
INFOBIP_DSN=infobip://YOUR_APIKEY@YOUR_API_HOST?from=SENDER_NAME
```

```yaml
# ./config/packages/notifier.yaml
framework:
    notifier:
        texter_transports:
            infobip: '%env(INFOBIP_DSN)%'
```

```yaml
# ./config/services.yaml
Siru\Notifier\Bridge\Infobip\InfobipTransportFactory:
    tags: [ texter.transport_factory ]
```