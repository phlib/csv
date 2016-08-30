<?php
namespace Phlib\Csv\Adapter;

/**
 * An adapter for when you already have a stream open to read CSV data from
 *
 * Class StreamAdapter
 * @package Phlib\Csv\Adapter
 */
class StreamAdapter implements AdapterInterface
{
    /** @var  resource */
    protected $stream;

    /**
     * StreamAdapter constructor.
     * @param resource $stream
     */
    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }
}