<?php
namespace Phlib\Csv\Tests;

use Phlib\Csv\Adapter\AdapterInterface;
use Phlib\Csv\Csv;

class CsvTest extends \PHPUnit_Framework_TestCase
{

    public function testMaxColumns()
    {
        // Test the default value
        $maxColumns = 1000;
        $csv = new Csv($this->getTestAdapter());
        $this->assertEquals($maxColumns, $csv->getMaxColumns());

        // Test changing the value
        $maxColumns = 500;
        $csv->setMaxColumns($maxColumns);
        $this->assertEquals($maxColumns, $csv->getMaxColumns());
    }

    public function testMaxColumnsInvalidArgument()
    {
        $csv = new Csv($this->getTestAdapter());

        $this->expectException(\InvalidArgumentException::class);
        $csv->setMaxColumns('Invalid');
    }

    public function testFetchMode()
    {
        $fetchMode = Csv::FETCH_ASSOC;
        $csv = new Csv($this->getTestAdapter());
        $this->assertEquals($fetchMode, $csv->getFetchMode());

        $fetchMode = Csv::FETCH_NUM;
        $csv->setFetchMode($fetchMode);
        $this->assertEquals($fetchMode, $csv->getFetchMode());

        $fetchMode = Csv::FETCH_ASSOC;
        $csv->setFetchMode($fetchMode);
        $this->assertEquals($fetchMode, $csv->getFetchMode());
    }

    public function testFetchModeInvalidArgument()
    {
        $csv = new Csv($this->getTestAdapter());

        $this->expectException(\InvalidArgumentException::class);
        $csv->setFetchMode('Invalid');
    }

    public function testHasHeader()
    {
        $emptyAdapter = $this->getTestAdapter();

        // Default value is false
        $csv = new Csv($emptyAdapter);
        $this->assertFalse($csv->hasHeader());

        // When set, will return the setting value even if data is empty
        $csv = new Csv($emptyAdapter, true);
        $this->assertTrue($csv->hasHeader());
    }


    public function testHeaders()
    {
        // Ensure headers are parsed correctly
        $csv = new Csv($this->getTestCsvAdapter(), true);
        $expectedResult = ['email', 'name'];
        $this->assertEquals($expectedResult, $csv->headers());

        $csv = new Csv($this->getTestCsvAdapter(), false);
        $expectedResult = [];
        $this->assertEquals($expectedResult, $csv->headers());
    }


    public function testCurrent()
    {
        $csv = new Csv($this->getTestCsvAdapter(), true);

        // Initial call will return the first record
        $expectedData = ['name' => 'Adam', 'email' => 'aw@example.com'];
        $this->assertEquals($expectedData, $csv->current());

        // The same data should be returned on subsequent calls to current
        $this->assertEquals($expectedData, $csv->current());
    }

    public function testNext()
    {
        $csv = new Csv($this->getTestCsvAdapter(), true);

        // Calling next before the iterator is initialised will set the pointer to the first record
        $expectedData = ['name' => 'Adam', 'email' => 'aw@example.com'];
        $csv->next();
        $this->assertEquals($expectedData, $csv->current());

        // Subsequent calls to next move the pointer to the next record
        $expectedData = ['name' => 'Luke', 'email' => 'lr@example.com'];
        $csv->next();
        $this->assertEquals($expectedData, $csv->current());

        // Moving the pointer beyond the end of the data returns false
        $csv->next();
        $this->assertFalse($csv->current());
    }

    public function testKey()
    {
        $csv = new Csv($this->getTestCsvAdapter(), true);

        $expectedValue = 0;
        $this->assertEquals($expectedValue, $csv->key());

        $csv->next();
        $expectedValue = 1;
        $this->assertEquals($expectedValue, $csv->key());

        // Going past the end of the collection returns null
        $csv->next();
        $this->assertNull($csv->key());
    }

    public function testValid()
    {
        $csv = new Csv($this->getTestCsvAdapter(), true);

        $csv->current();
        $this->assertTrue($csv->valid());

        $csv->next();
        $this->assertTrue($csv->valid());

        $csv->next();
        $this->assertFalse($csv->valid());
    }

    public function testRewind()
    {
        $csv = new Csv($this->getTestCsvAdapter(), true);
        $csv->next();
        $csv->next();
        $csv->rewind();

        $expectedData = ['name' => 'Adam', 'email' => 'aw@example.com'];
        $this->assertEquals($expectedData, $csv->current());
    }

    public function testCount()
    {
        $csv = new Csv($this->getTestCsvAdapter(), true);
        $expectedCount = 2;
        $this->assertEquals($expectedCount, $csv->count());

        // The stream should still be accessible after calling count
        $expectedData = ['name' => 'Adam', 'email' => 'aw@example.com'];
        $this->assertEquals($expectedData, $csv->current());


        // Create a new CSV to reset the count
        $csv = new Csv($this->getTestCsvAdapter(), true);

        // Count should still be valid when the file pointer is not at the first record
        $csv->next();
        $csv->next();
        $expectedCount = 2;
        $this->assertEquals($expectedCount, $csv->count());

        // After calling count, the CSV should still be aware of it's position
        $expectedData = ['name' => 'Luke', 'email' => 'lr@example.com'];
        $this->assertEquals($expectedData, $csv->current());
    }

    protected function getTestCsvAdapter()
    {
        $csv = <<<CSV
email,name
aw@example.com,Adam
lr@example.com,Luke
CSV;
        return $this->getTestAdapter($csv);
    }

    protected function getTestAdapter($data = '')
    {
        $stream = fopen('php://memory','r+');
        fwrite($stream, $data);
        rewind($stream);

        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('getStream')->willReturn($stream);
        $adapter->method('closeStream')->will($this->returnCallback(function() use ($stream) {
            rewind($stream);
        }));

        return $adapter;
    }
}
