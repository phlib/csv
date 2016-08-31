<?php
namespace Phlib\Csv\Tests;

use Phlib\Csv\Adapter\AdapterInterface;
use Phlib\Csv\Csv;
use Psr\Http\Message\StreamInterface;

class CsvTest extends \PHPUnit_Framework_TestCase
{
    use CreateStreamTrait;

    public function testMaxColumns()
    {
        // Test the default value
        $maxColumns = 1000;
        $csv = new Csv($this->getStreamInterface());
        $this->assertEquals($maxColumns, $csv->getMaxColumns());

        // Test changing the value
        $maxColumns = 500;
        $csv->setMaxColumns($maxColumns);
        $this->assertEquals($maxColumns, $csv->getMaxColumns());
    }

    public function testMaxColumnsInvalidArgument()
    {
        $csv = new Csv($this->getStreamInterface());

        $this->expectException(\InvalidArgumentException::class);
        $csv->setMaxColumns('Invalid');
    }

    public function testFetchMode()
    {
        $fetchMode = Csv::FETCH_ASSOC;
        $csv = new Csv($this->getStreamInterface());
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
        $csv = new Csv($this->getStreamInterface());

        $this->expectException(\InvalidArgumentException::class);
        $csv->setFetchMode('Invalid');
    }

    public function testHasHeader()
    {
        $emptyAdapter = $this->getStreamInterface();

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
        $csv = new Csv($this->getTestCsvStreamInterface(), true);
        $expectedResult = ['email', 'name'];
        $this->assertEquals($expectedResult, $csv->headers());

        $csv = new Csv($this->getTestCsvStreamInterface(), false);
        $expectedResult = [];
        $this->assertEquals($expectedResult, $csv->headers());
    }


    public function testCurrent()
    {
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

        // Initial call will return the first record
        $expectedData = ['name' => 'Adam', 'email' => 'aw@example.com'];
        $this->assertEquals($expectedData, $csv->current());

        // The same data should be returned on subsequent calls to current
        $this->assertEquals($expectedData, $csv->current());
    }

    public function testNext()
    {
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

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
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

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
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

        $csv->current();
        $this->assertTrue($csv->valid());

        $csv->next();
        $this->assertTrue($csv->valid());

        $csv->next();
        $this->assertFalse($csv->valid());
    }

    public function testRewind()
    {
        $csv = new Csv($this->getTestCsvStreamInterface(), true);
        $csv->next();
        $csv->next();
        $csv->rewind();

        $expectedData = ['name' => 'Adam', 'email' => 'aw@example.com'];
        $this->assertEquals($expectedData, $csv->current());
    }

    public function testCount()
    {
        $csv = new Csv($this->getTestCsvStreamInterface());
        $expectedCount = 3;
        $this->assertEquals($expectedCount, $csv->count());

        $csv = new Csv($this->getTestCsvStreamInterface(), true);
        $expectedCount = 2;
        $this->assertEquals($expectedCount, $csv->count());

        // The stream should still be accessible after calling count
        $expectedData = ['name' => 'Adam', 'email' => 'aw@example.com'];
        $this->assertEquals($expectedData, $csv->current());


        // Create a new CSV to reset the count
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

        // Count should still be valid when the file pointer is not at the first record
        $csv->next();
        $csv->next();
        $expectedCount = 2;
        $this->assertEquals($expectedCount, $csv->count());

        // After calling count, the CSV should still be aware of it's position
        $expectedData = ['name' => 'Luke', 'email' => 'lr@example.com'];
        $this->assertEquals($expectedData, $csv->current());
    }

    public function testMoreHeadersThanRowColumns()
    {
        // Additional missing fields from the headers will be defaulted
        $csvData = <<<CSV
email,name,phone
aw@example.com,Adam
lr@example.com,Luke
CSV;
        $csv = new Csv($this->getStreamInterface($csvData), true);
        $expectedData = ['name' => 'Adam', 'email' => 'aw@example.com', 'phone' => null];
        $this->assertEquals($expectedData, $csv->current());
    }

    public function testMoreRowColumnsThanHeaders()
    {
        // Additional fields than the headers will be ignored
        $csvData = <<<CSV
email,name
aw@example.com,Adam,123
lr@example.com,Luke,123,456
CSV;
        $csv = new Csv($this->getStreamInterface($csvData), true);

        $this->expectException(\DomainException::class);
        $csv->current();
    }

    /**
     * @return StreamInterface
     */
    protected function getTestCsvStreamInterface()
    {
        $csv = <<<CSV
email,name
aw@example.com,Adam
lr@example.com,Luke
CSV;
        return $this->getStreamInterface($csv);
    }

    /**
     * @param string $data
     * @return StreamInterface
     */
    protected function getStreamInterface($data = '')
    {
        $stream = $this->createStream($data);
        $streamInterface = $this->getMockStreamInterface($stream);
        return $streamInterface;
    }

    /**
     * @param $stream
     * @return StreamInterface
     */
    protected function getMockStreamInterface($stream)
    {
        $streamInterface = $this->createMock(StreamInterface::class);
        $streamInterface->method('rewind')->will($this->returnCallback(function() use ($stream) {
            rewind($stream);
        }));
        $streamInterface->method('tell')->will($this->returnCallback(function() use ($stream) {
            return ftell($stream);
        }));
        $streamInterface->method('seek')->will($this->returnCallback(function($offset) use ($stream) {
            fseek($stream, $offset);
        }));
        $streamInterface->method('read')->will($this->returnCallback(function($length) use ($stream) {
            return fread($stream, $length);
        }));
        $streamInterface->method('eof')->will($this->returnCallback(function() use ($stream) {
            return feof($stream);
        }));
        return $streamInterface;
    }
}
