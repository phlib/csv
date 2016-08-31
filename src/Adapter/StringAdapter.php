<?php
namespace Phlib\Csv\Adapter;


class StringAdapter implements AdapterInterface
{
    /** @var  string */
    protected $content;

    /** @var  resource */
    protected $stream;

    /**
     * StringAdapter constructor.
     * @param string $content
     */
    public function __construct($content)
    {
        $this->content = $content;
    }

    public function getStream()
    {
        if (!$this->stream) {
            $stream = fopen('php://temp', 'w+');
            fwrite($stream, $this->content);
            rewind($stream);

            $this->stream = $stream;
        }

        return $this->stream;
    }

    public function closeStream()
    {
        fclose($this->stream);
        $this->stream = null;
    }
}