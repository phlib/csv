<?php
namespace Phlib\Csv;


use Phlib\Csv\Adapter\AdapterInterface;

class Csv implements \Iterator, \Countable
{
    protected $stream;

    /** @var  AdapterInterface */
    protected $adapter;

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
    }

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
        return 0;
    }

    /**
     * @param int $maxColumns
     */
    public function setMaxColumns($maxColumns)
    {

    }

    /**
     * Set the fetch mode.
     *
     * @param integer $mode
     * @return void
     */
    public function setFetchMode($mode)
    {

    }

    /**
     * Get the fetch mode.
     *
     * @return int
     */
    public function getFetchMode()
    {
        return 0;
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