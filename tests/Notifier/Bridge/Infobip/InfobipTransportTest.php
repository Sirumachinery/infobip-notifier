<?php
declare(strict_types=1);

namespace Siru\Tests\Notifier\Bridge\Infobip;

use PHPUnit\Framework\TestCase;
use Siru\Notifier\Bridge\Infobip\InfobipTransport;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\SmsMessage;

class InfobipTransportTest extends TestCase
{

    public function testSupports()
    {
        $message = new SmsMessage('1234', 'My message');
        $transport = new InfobipTransport('key', 'from');
        $this->assertTrue($transport->supports($message));
    }

    public function testDoesNotSupportChatMessage()
    {
        $message = new ChatMessage('1234');
        $transport = new InfobipTransport('key', 'from');
        $this->assertFalse($transport->supports($message));
    }

    public function testToString()
    {
        $transport = new InfobipTransport('key', 'from');
        $this->assertEquals('infobip://api.infobip.com?from=from', (string) $transport);
    }

    public function testInvalidMessage()
    {
        $message = new ChatMessage('1234');
        $transport = new InfobipTransport('key', 'from');
        $this->expectException(LogicException::class);
        $transport->send($message);
    }

    public function testSendsMessage()
    {
        $requestCount = 0;
        $callback = function($method, $url, $options) use (&$requestCount) {
            $this->assertSame('POST', $method);
            $this->assertSame('https://api.infobip.com/sms/2/text/advanced', $url);
            $this->assertArrayHasKey('headers', $options);
            $this->assertContains('Authorization: App key', $options['headers']);
            $this->assertContains('Content-Type: application/json', $options['headers']);
            $this->assertEquals('{"messages":[{"from":"from","destinations":[{"to":"1234"}],"text":"My message","notifyUrl":"https:\/\/localhost\/notify"}]}', $options['body']);

            $requestCount++;
            return new MockResponse('{"bulkId": "2034072219640523072","messages": [{"to": "1234","status": {"groupId": 1,"groupName": "PENDING","id": 26,"name": "MESSAGE_ACCEPTED","description": "Message sent to next instance"},"messageId": "2250be2d4219-3af1-78856-aabe-1362af1edfd2"}]}');
        };

        $client = new MockHttpClient($callback);
        $message = new SmsMessage('1234', 'My message');
        $transport = new InfobipTransport('key', 'from', $client);
        $transport->setNotifyUrl('https://localhost/notify');
        $sentMessage = $transport->send($message);

        $this->assertEquals(1, $requestCount);
        $this->assertEquals('2250be2d4219-3af1-78856-aabe-1362af1edfd2', $sentMessage->getMessageId());
        $this->assertSame($message, $sentMessage->getOriginalMessage());
        $this->assertEquals((string) $transport, $sentMessage->getTransport());
    }

    public function testApiError()
    {
        $client = new MockHttpClient(new MockResponse('{"requestError":{"serviceException":{"additionalDescription":"","text":"","variables":"","messageId":""}}}', ['http_code' => 500]));
        $message = new SmsMessage('1234', 'My message');

        $this->expectException(TransportException::class);

        $transport = new InfobipTransport('key', 'from', $client);
        $transport->send($message);
    }

}