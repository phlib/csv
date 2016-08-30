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
        $this->hasHeader = $hasHeader;
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
        return true;
    }

    /**
     * Returns array of the headers, empty if no headers
     *
     * @return array Headers
     */
    public function headers()
    {
        return [];
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


}