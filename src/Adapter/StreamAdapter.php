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

    public function closeStream()
    {
        // This is a passthrough adapter - it's up to it's creator to safely open/close streams
        // Best we can do is rewind it to the default position
        rewind($this->stream);
    }


}