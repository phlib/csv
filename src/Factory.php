<?php

declare(strict_types=1);

namespace Phlib\Csv;

use function GuzzleHttp\Psr7\stream_for;

class Factory
{
    public static function createFromFile(
        string $filename,
        bool $hasHeader = false,
        string $delimiter = ',',
        string $enclosure = '"'
    ) {
        $resource = @fopen($filename, 'r');
        if (!$resource) {
            $error = error_get_last();
            throw new \RuntimeException(
                sprintf(
                    'Failed to open handle to "%s", reason "%s"',
                    $filename,
                    trim($error['message'])
                )
            );
        }

        return new Csv(stream_for($resource), $hasHeader, $delimiter, $enclosure);
    }

    public static function createFromZipFile(
        string $filename,
        bool $hasHeader = false,
        string $delimiter = ',',
        string $enclosure = '"'
    ) {
        $zip = new \ZipArchive();
        if ($zip->open($filename) !== true) {
            throw new \RuntimeException(
                sprintf(
                    'Failed to open Zip file "%s"',
                    $filename
                )
            );
        }

        $name = $zip->getNameIndex(0);
        if (!$name) {
            throw new \RuntimeException(
                sprintf(
                    'Failed to locate entry within Zip file "%s"',
                    $filename
                )
            );
        }
        $zipStream = $zip->getStream($name);

        // Zip file stream is not seekable, so copy to a temporary stream
        // Writing in chunks rather than using stream_copy_to_stream() which is not memory-safe
        $resource = fopen('php://temp', 'w+b');
        while (!feof($zipStream)) {
            fwrite($resource, fread($zipStream, 131072)); // 128 KB
        }
        fclose($zipStream);
        $zip->close();
        rewind($resource);

        return new Csv(stream_for($resource), $hasHeader, $delimiter, $enclosure);
    }
}
