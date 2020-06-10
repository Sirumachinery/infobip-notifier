# Infobip Notifier

Provides Infobip integration for Symfony Notifier.

## Requirements

- PHP 7.2
- Symfony Notifier, HttpClient and EventDispatcher components
- Your API key and base url from Infobip

## Installation

```shell script
$ composer require sirumobile/infobip-notifier
```

Add correct DSN with your Infobip credentials to ENV. Then configure notifier and
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

## Delivery reports

You can add notifyUrl-option where Infobip will send delivery reports for each message. You will
need to implement the callback controller yourself. Check Infobip API documentation for example payload.

```dotenv
# .env
INFOBIP_DSN=infobip://YOUR_APIKEY@YOUR_API_HOST?from=SENDER_NAME&notifyUrl=https://yourapplication/callback/path
```