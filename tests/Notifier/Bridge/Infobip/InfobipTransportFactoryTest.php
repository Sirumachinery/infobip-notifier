<?php
declare(strict_types=1);

namespace Siru\Tests\Notifier\Bridge\Infobip;

use PHPUnit\Framework\TestCase;
use Siru\Notifier\Bridge\Infobip\InfobipTransport;
use Siru\Notifier\Bridge\Infobip\InfobipTransportFactory;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\Dsn;

class InfobipTransportFactoryTest extends TestCase
{

    public function testSupportedSchemes()
    {
        $factory = new InfobipTransportFactory();
        $this->assertTrue($factory->supports(new Dsn('infobip', 'localhost')));
        $this->assertFalse($factory->supports(new Dsn('foobar', 'localhost')));
    }

    public function testCreatesTransport()
    {
        $dsn = new Dsn('infobip', 'localhost', 'apikey', null, null, ['from' => 'myname']);
        $factory = new InfobipTransportFactory();

        $transport = $factory->create($dsn);
        $this->assertInstanceOf(InfobipTransport::class, $transport);
    }

    public function testInvalidScheme()
    {
        $dsn = new Dsn('foobar', 'localhost', 'apikey', null, null, ['from' => 'myname']);
        $factory = new InfobipTransportFactory();

        $this->expectException(UnsupportedSchemeException::class);
        $transport = $factory->create($dsn);
    }

}