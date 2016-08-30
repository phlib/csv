<?php
namespace Phlib\Csv\Tests;

trait CreateStreamTrait
{
    protected function createStream($data = '')
    {
        $stream = fopen('php://memory','r+');
        fwrite($stream, $data);
        rewind($stream);
        return $stream;
    }
}