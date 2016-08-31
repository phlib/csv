<?php
namespace Phlib\Csv\Adapter;


class FileAdapter implements AdapterInterface
{
    /** @var  string */
    protected $filename;

    /** @var  resource */
    protected $stream;

    /**
     * FileAdapter constructor.
     * @param string $filename
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }


    public function getStream()
    {
        if (!$this->stream) {
            $stream = @fopen($this->filename, 'r');
            if (!$stream) {
                $error = error_get_last();
                throw new \RuntimeException(
                    sprintf(
                        'Could not open file: Failed to open handle to "%s", reason "%s"',
                        $this->filename,
                        trim($error['message'])
                    )
                );
            }
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