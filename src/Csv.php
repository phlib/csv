<?php

declare(strict_types=1);

namespace Phlib\Csv;

use Psr\Http\Message\StreamInterface;

class Csv implements \Iterator, \Countable
{
    public const FETCH_ASSOC = 1;

    public const FETCH_NUM = 2;

    private StreamInterface $stream;

    private bool $hasHeader;

    private string $delimiter;

    private string $enclosure;

    private int $maxColumns = 1000;

    private int $fetchMode = self::FETCH_ASSOC;

    private string $regex;

    private int $rowSize = 24576;

    private array $headers;

    private string $buffer = '';

    private ?int $position = 0;

    /**
     * @var array|false|null
     */
    private $current;

    private int $count;

    public function __construct(
        StreamInterface $stream,
        bool $hasHeader = false,
        string $delimiter = ',',
        string $enclosure = '"'
    ) {
        if (!$stream->isSeekable()) {
            throw new \InvalidArgumentException('Stream is not seekable');
        }

        $this->stream = $stream;
        $this->hasHeader = $hasHeader;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }

    public function getMaxColumns(): int
    {
        return $this->maxColumns;
    }

    public function setMaxColumns(int $maxColumns): void
    {
        $options = [
            'options' => [
                'min_range' => 1,
                'max_range' => PHP_INT_MAX,
            ],
        ];
        $invalid = (filter_var($maxColumns, FILTER_VALIDATE_INT, $options) === false);
        if ($invalid) {
            throw new \InvalidArgumentException("Invalid max columns, {$maxColumns}");
        }

        $this->maxColumns = $maxColumns;
    }

    public function setFetchMode(int $mode): void
    {
        $validModes = [self::FETCH_ASSOC, self::FETCH_NUM];
        if (!in_array($mode, $validModes, true)) {
            throw new \InvalidArgumentException('Unrecognised fetch mode requested.');
        }

        $this->fetchMode = $mode;
    }

    public function getFetchMode(): int
    {
        return $this->fetchMode;
    }

    public function hasHeader(): bool
    {
        return $this->hasHeader;
    }

    public function headers(): array
    {
        if (!isset($this->headers)) {
            // Headers a loaded as part of the rewind method
            $this->rewind();
        }

        return $this->headers;
    }

    /**
     * @return array|false|null
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        if ($this->current === null) {
            $this->rewind();
        }

        $current = $this->current;
        if ($this->hasHeader and is_array($this->headers) and is_array($current) and $this->fetchMode === self::FETCH_ASSOC) {
            $headers = $this->headers;

            // PHP7 Spaceship Operator could work here
            if (count($headers) < count($current)) {
                throw new \DomainException('Row has more columns than headers');
            } elseif (count($headers) > count($current)) {
                // Headers are too long - pad the columns
                $current = array_pad($current, count($headers), null);
            }

            $current = array_combine($headers, $current);
        }

        return $current;
    }

    public function next(): void
    {
        if ($this->current === null) {
            $this->rewind();
            return;
        }

        $this->position++;
        $this->current = $this->fetchLine($this->stream, $this->buffer);

        if ($this->current === false) {
            // If there is no current record, we have no valid position
            $this->position = null;
        }
    }

    public function key(): ?int
    {
        if ($this->current === null) {
            $this->rewind();
        }
        return $this->position;
    }

    public function valid(): bool
    {
        return ($this->current !== false);
    }

    public function rewind(): void
    {
        $this->buffer = '';
        $this->stream->rewind();
        $this->position = 0;

        $this->headers = [];
        if ($this->hasHeader()) {
            $line = $this->fetchLine($this->stream, $this->buffer);
            if ($line !== false) {
                $this->headers = $line;
            }
        }
        $this->current = $this->fetchLine($this->stream, $this->buffer);
    }

    public function count(): int
    {
        if (!isset($this->count)) {
            // Store the pointer position and reset the file handle
            $position = $this->stream->tell();
            $this->stream->rewind();

            // Count the rows
            $count = 0;
            $buffer = '';
            while ($this->fetchLine($this->stream, $buffer)) {
                $count++;
            }

            // Store the count
            $this->count = $count;

            // Reduce the count by one if a headers row is present
            if ($this->hasHeader() and $this->count > 0) {
                $this->count--;
            }

            // Restore the file handle to its previous position
            $this->stream->seek($position);
        }
        return $this->count;
    }

    private function getRegex(): string
    {
        if (!isset($this->regex)) {
            $enclosure = preg_quote($this->enclosure);
            $delimiter = preg_quote($this->delimiter);

            $enclosureField = "{$enclosure}[^{$enclosure}]*(?:{$enclosure}{$enclosure}[^{$enclosure}]*)*{$enclosure}";
            $plainField = "[^\r\n{$delimiter}]*";
            $delimiterOrNewLine = "({$delimiter}|\r\n|\n|\r|$)";

            $this->regex = "/({$enclosureField}|{$plainField}){$delimiterOrNewLine}/";
        }
        return $this->regex;
    }

    /**
     * @return array|bool
     */
    private function fetchLine(StreamInterface $stream, string &$buffer)
    {
        $enclosure = $this->enclosure;

        // fill the buffer with our max row size
        $buffer .= $stream->read($this->rowSize - strlen($buffer));
        $bufferSize = strlen($buffer);

        // check if we've got to the end of the file and the buffer is empty
        if ($bufferSize === 0 and $stream->eof() === true) {
            // we've finished everything we can do
            return false;
        }

        // Check for UTF-8 BOM and remove
        // > The Unicode Standard permits the BOM in UTF-8, but does not require or recommend its use.
        // > Byte order has no meaning in UTF-8, so its only use in UTF-8 is to
        // > signal at the start that the text stream is encoded in UTF-8.
        // > https://en.wikipedia.org/wiki/Byte_order_mark#UTF-8
        $offset = 0;
        if (substr($buffer, 0, 3) === "\xEF\xBB\xBF") {
            $offset = 3;
        }

        // prepare the regex
        $regex = $this->getRegex();

        $idx = 0;
        $row = [];
        while ($offset < $bufferSize) {
            // use the regex to pull out matches
            $matches = null;
            $results = preg_match($regex, $buffer, $matches, PREG_OFFSET_CAPTURE, $offset);

            // if we didn't get any results, or the offset doesn't match then things aren't valid
            if ($results === 0 or $matches[0][1] !== $offset) {
                // TODO $row, $offset, sample
                throw new \DomainException(
                    sprintf(
                        'Cannot read CSV data: invalid field at character position %d',
                        $offset + 1
                    )
                );
            }

            $value = $matches[1][0];
            $delimiter = $matches[2][0];
            $offset = $matches[2][1] + strlen($delimiter);

            // if we've matched an enclosure then remove them
            if (isset($value[0]) and $value[0] === $enclosure and substr($value, -1) === $enclosure) {
                $value = substr($value, 1, -1);
                // An enclosed field may contain escaped enclosures
                $value = str_replace($enclosure . $enclosure, $enclosure, $value);
            }

            // store the value
            $row[$idx++] = $value;

            if ($idx > $this->maxColumns) {
                throw new \DomainException('Cannot read CSV data: too many columns found');
            }

            // check the delimiter and break out if we've reached the end
            switch ($delimiter) {
                case "\r\n":
                case "\n":
                case "\r":
                case '':
                    break 2;
            }
        }

        // shift the value we've used off the buffer
        $buffer = substr($buffer, $offset);

        // return the row
        return $row;
    }
}
