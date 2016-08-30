<?php
namespace Phlib\Csv;


use Phlib\Csv\Adapter\AdapterInterface;

class Csv implements \Iterator, \Countable
{
    const FETCH_ASSOC = 1;
    const FETCH_NUM   = 2;

    protected $stream;

    /** @var  AdapterInterface */
    protected $adapter;

    /** @var  bool */
    protected $hasHeader;

    /** @var  string */
    protected $delimiter;

    /** @var  string */
    protected $enclosure;

    /** @var  int */
    protected $maxColumns = 1000;

    /** @var  int  */
    protected $fetchMode = self::FETCH_ASSOC;

    /** @var  string */
    protected $regex;

    /** @var int  */
    protected $rowSize = 24576;

    protected $headers = [];

    /**
     * Csv constructor.
     * @param AdapterInterface $adapter
     * @param bool $hasHeader
     * @param string $delimiter
     * @param string $enclosure
     */
    public function __construct(AdapterInterface $adapter, $hasHeader = false, $delimiter = ',', $enclosure = '"')
    {
        $this->adapter = $adapter;
        $this->hasHeader = (bool)$hasHeader;
        $this->delimiter = $delimiter;
        $this->enclosure = $enclosure;
    }

    /**
     * Loads a stream from the adapter.
     *
     * @return resource
     */
    protected function getStream()
    {
        if (!$this->stream) {
            $this->stream = $this->adapter->getStream();
        }
        return $this->stream;
    }

    /**
     * @return int
     */
    public function getMaxColumns()
    {
        return $this->maxColumns;
    }

    /**
     * @param int $maxColumns
     */
    public function setMaxColumns($maxColumns)
    {
        $options = ['options' => ['min_range' => 1, 'max_range' => PHP_INT_MAX]];
        $invalid = (filter_var($maxColumns, FILTER_VALIDATE_INT, $options) === false);
        if ($invalid) {
            throw new \InvalidArgumentException("Invalid max columns, $maxColumns");
        }

        $this->maxColumns = (int)$maxColumns;
    }

    /**
     * Set the fetch mode.
     *
     * @param integer $mode
     * @return void
     */
    public function setFetchMode($mode)
    {
        $validModes = [self::FETCH_ASSOC, self::FETCH_NUM];
        if (!in_array($mode, $validModes)) {
            throw new \InvalidArgumentException('Unrecognised fetch mode requested.');
        }

        $this->fetchMode = $mode;

    }

    /**
     * Get the fetch mode.
     *
     * @return int
     */
    public function getFetchMode()
    {
        return $this->fetchMode;
    }

    /**
     * Returns true if the csv data has headers, false otherwise.
     *
     * @return boolean
     */
    public function hasHeader()
    {
        return $this->hasHeader;
    }

    /**
     * Returns array of the headers, empty if no headers
     *
     * @return array Headers
     */
    public function headers()
    {
        if ($this->headers === null) {
            // Headers a loaded as part of the rewind method
            $this->rewind();
        }

        return $this->headers;
    }

    public function current()
    {
        // TODO: Implement current() method.
    }

    public function next()
    {
        // TODO: Implement next() method.
    }

    public function key()
    {
        // TODO: Implement key() method.
    }

    public function valid()
    {
        // TODO: Implement valid() method.
    }

    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * @return string
     */
    protected function getRegex()
    {
        if (!$this->regex) {
            $enclosure = preg_quote($this->enclosure);
            $delimiter = preg_quote($this->delimiter);

            $enclosureField = "{$enclosure}[^{$enclosure}]*(?:{$enclosure}{$enclosure}[^{$enclosure}]*)*{$enclosure}";
            $plainField = "[^\r\n{$delimiter}]*";
            $delimiterOrNewLine = "({$delimiter}|\r\n|\n|\r|$)";

            $this->regex = "/({$enclosureField}|{$plainField}){$delimiterOrNewLine}/";
        }
        return $this->regex;
    }

    protected function fetchLine($handle, &$buffer)
    {
        $enclosure = $this->enclosure;

        // fill the buffer with our max row size
        $buffer .= fread($handle, $this->rowSize - strlen($buffer));
        $bufferSize = strlen($buffer);

        // check if we've got to the end of the file and the buffer is empty
        if ($bufferSize === 0 and feof($handle) === true) {

            // we've finished everything we can do
            return false;
        }

        // prepare the regex
        $regex = $this->getRegex();

        $idx    = 0;
        $row    = array();
        $offset = 0;
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

            $value     = $matches[1][0];
            $delimiter = $matches[2][0];
            $offset    = $matches[2][1] + strlen($delimiter);

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