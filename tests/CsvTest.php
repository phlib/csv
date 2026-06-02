<?php

declare(strict_types=1);

namespace Phlib\Csv\Tests;

use GuzzleHttp\Psr7\Utils;
use Phlib\Csv\Csv;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class CsvTest extends TestCase
{
    public function testStreamNotSeekable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not seekable');

        $stream = $this->createMock(StreamInterface::class);
        $stream->expects(static::once())
            ->method('isSeekable')
            ->willReturn(false);

        new Csv($stream);
    }

    public function testMaxColumns(): void
    {
        // Test the default value
        $maxColumns = 1000;
        $csv = new Csv(Utils::streamFor(''));
        static::assertEquals($maxColumns, $csv->getMaxColumns());

        // Test changing the value
        $maxColumns = 500;
        $csv->setMaxColumns($maxColumns);
        static::assertEquals($maxColumns, $csv->getMaxColumns());
    }

    public function testMaxColumnsInvalidArgument(): void
    {
        $csv = new Csv(Utils::streamFor(''));

        $this->expectException(\InvalidArgumentException::class);
        $csv->setMaxColumns(-1);
    }

    public function testFetchMode(): void
    {
        $fetchMode = Csv::FETCH_ASSOC;
        $csv = new Csv(Utils::streamFor(''));
        static::assertEquals($fetchMode, $csv->getFetchMode());

        $fetchMode = Csv::FETCH_NUM;
        $csv->setFetchMode($fetchMode);
        static::assertEquals($fetchMode, $csv->getFetchMode());

        $fetchMode = Csv::FETCH_ASSOC;
        $csv->setFetchMode($fetchMode);
        static::assertEquals($fetchMode, $csv->getFetchMode());
    }

    public function testFetchModeInvalidArgument(): void
    {
        $csv = new Csv(Utils::streamFor(''));

        $this->expectException(\InvalidArgumentException::class);
        $csv->setFetchMode(3);
    }

    public function testHasHeader(): void
    {
        $emptyAdapter = Utils::streamFor('');

        // Default value is false
        $csv = new Csv($emptyAdapter);
        static::assertFalse($csv->hasHeader());

        // When set, will return the setting value even if data is empty
        $csv = new Csv($emptyAdapter, true);
        static::assertTrue($csv->hasHeader());
    }

    public function testHeaders(): void
    {
        // Ensure headers are parsed correctly
        $csv = new Csv($this->getTestCsvStreamInterface(), true);
        $expectedResult = ['email', 'name'];
        static::assertEquals($expectedResult, $csv->headers());

        $csv = new Csv($this->getTestCsvStreamInterface(), false);
        $expectedResult = [];
        static::assertEquals($expectedResult, $csv->headers());
    }

    public function testCurrent(): void
    {
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

        // Initial call will return the first record
        $expectedData = [
            'name' => 'One',
            'email' => 'test1@example.com',
        ];
        static::assertEquals($expectedData, $csv->current());

        // The same data should be returned on subsequent calls to current
        static::assertEquals($expectedData, $csv->current());
    }

    public function testNext(): void
    {
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

        // Calling next before the iterator is initialised will set the pointer to the first record
        $expectedData = [
            'name' => 'One',
            'email' => 'test1@example.com',
        ];
        $csv->next();
        static::assertEquals($expectedData, $csv->current());

        // Subsequent calls to next move the pointer to the next record
        $expectedData = [
            'name' => 'Two',
            'email' => 'test2@example.com',
        ];
        $csv->next();
        static::assertEquals($expectedData, $csv->current());

        // Moving the pointer beyond the end of the data returns false
        $csv->next();
        static::assertFalse($csv->current());
    }

    public function testKey(): void
    {
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

        $expectedValue = 0;
        static::assertEquals($expectedValue, $csv->key());

        $csv->next();
        $expectedValue = 1;
        static::assertEquals($expectedValue, $csv->key());

        // Going past the end of the collection returns null
        $csv->next();
        static::assertNull($csv->key());
    }

    public function testValid(): void
    {
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

        $csv->current();
        static::assertTrue($csv->valid());

        $csv->next();
        static::assertTrue($csv->valid());

        $csv->next();
        static::assertFalse($csv->valid());
    }

    public function testRewind(): void
    {
        $csv = new Csv($this->getTestCsvStreamInterface(), true);
        $csv->next();
        $csv->next();
        $csv->rewind();

        $expectedData = [
            'name' => 'One',
            'email' => 'test1@example.com',
        ];
        static::assertEquals($expectedData, $csv->current());
    }

    public function testCount(): void
    {
        $csv = new Csv($this->getTestCsvStreamInterface());
        $expectedCount = 3;
        static::assertEquals($expectedCount, $csv->count());

        $csv = new Csv($this->getTestCsvStreamInterface(), true);
        $expectedCount = 2;
        static::assertEquals($expectedCount, $csv->count());

        // The stream should still be accessible after calling count
        $expectedData = [
            'name' => 'One',
            'email' => 'test1@example.com',
        ];
        static::assertEquals($expectedData, $csv->current());

        // Create a new CSV to reset the count
        $csv = new Csv($this->getTestCsvStreamInterface(), true);

        // Count should still be valid when the file pointer is not at the first record
        $csv->next();
        $csv->next();
        $expectedCount = 2;
        static::assertEquals($expectedCount, $csv->count());

        // After calling count, the CSV should still be aware of it's position
        $expectedData = [
            'name' => 'Two',
            'email' => 'test2@example.com',
        ];
        static::assertEquals($expectedData, $csv->current());
    }

    public function testMoreHeadersThanRowColumns(): void
    {
        // Additional missing fields from the headers will be defaulted
        $csvData = <<<CSV
email,name,phone
test1@example.com,One
test2@example.com,Two
CSV;
        $csv = new Csv(Utils::streamFor($csvData), true);
        $expectedData = [
            'name' => 'One',
            'email' => 'test1@example.com',
            'phone' => null,
        ];
        static::assertEquals($expectedData, $csv->current());
    }

    public function testMoreRowColumnsThanHeaders(): void
    {
        // Additional fields than the headers will be ignored
        $csvData = <<<CSV
email,name
test1@example.com,One,123
test2@example.com,Two,123,456
CSV;
        $csv = new Csv(Utils::streamFor($csvData), true);

        $this->expectException(\DomainException::class);
        $csv->current();
    }

    public function testStartsWithBomb(): void
    {
        // Additional fields than the headers will be ignored
        $csvData = <<<CSV
\xEF\xBB\xBFemail,name
test1@example.com,One
test2@example.com,Two
CSV;
        $csv = new Csv(Utils::streamFor($csvData), true);

        $expected = [
            'email' => 'test1@example.com',
            'name' => 'One',
        ];
        static::assertSame($expected, $csv->current());
    }

    private function getTestCsvStreamInterface(): StreamInterface
    {
        $csv = <<<CSV
email,name
test1@example.com,"One"
test2@example.com,"Two"
CSV;
        return Utils::streamFor($csv);
    }
}
