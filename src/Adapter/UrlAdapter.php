<?php
namespace Phlib\Csv\Adapter;


class UrlAdapter extends FileAdapter
{
    /** @var  string */
    protected $url;

    /**
     * UrlAdapter constructor.
     * @param string $url
     */
    public function __construct($url)
    {
        $this->url = $url;
        $filename = tempnam(sys_get_temp_dir(), __CLASS__);
        parent::__construct($filename);
    }


    public function getStream()
    {
        if (!$this->stream) {
            $this->download($this->url, $this->filename);
        }

        $stream = parent::getStream();
        return $stream;
    }

    public function closeStream()
    {
        parent::closeStream();
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }

    protected function download($url, $file)
    {
        $urlHandle = @fopen($url, 'r');
        if (!$urlHandle) {
            $error = error_get_last();
            throw new \RuntimeException(
                sprintf(
                    'Could not download file: Failed to open handle to "%s", reason "%s"',
                    $url,
                    trim($error['message'])
                )
            );
        }

        file_put_contents($file, $urlHandle);
        fclose($urlHandle);
    }
}