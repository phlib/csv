<?php
namespace Phlib\Csv\Tests\Adapter;

use Phlib\Csv\Adapter\StreamAdapter;
use Phlib\Csv\Tests\CreateStreamTrait;

class StreamAdapterTest extends \PHPUnit_Framework_TestCase
{
    use CreateStreamTrait;

    public function testGetStream()
    {
        $stream = $this->createStream();
        $adapter = new StreamAdapter($stream);

        $this->assertSame($stream, $adapter->getStream());
    }

    public function testCloseStream()
    {
        $stream = $this->createStream('This is a test stream!');
        $adapter = new StreamAdapter($stream);

        // Move the stream to the EOF
        fseek($stream, 0, SEEK_END);
        $this->assertGreaterThan(0, ftell($adapter->getStream()));

        // After closing, the stream should be rewound
        $adapter->closeStream();
        $this->assertEquals(0, ftell($adapter->getStream()));
    }
}
