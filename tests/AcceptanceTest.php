<?php

declare(strict_types=1);

namespace Phlib\Csv\Tests;

use GuzzleHttp\Psr7\Utils;
use Phlib\Csv\Csv;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AcceptanceTest extends TestCase
{
    public static function dataSampleFile(): array
    {
        return [
            'lineEndCr' => ['lineend-cr.csv', 'lineend.php'],
            'lineEndCrLf' => ['lineend-crlf.csv', 'lineend.php'],
            'lineEndLf' => ['lineend-lf.csv', 'lineend.php'],
            'withQuote' => ['quote-with.csv', 'quote-with.php'],
            'withoutQuote' => ['quote-without.csv', 'quote-without.php'],
        ];
    }

    #[DataProvider('dataSampleFile')]
    public function testSampleFile(string $csvFilename, string $expectedFilename): void
    {
        $fh = @fopen(__DIR__ . '/_files/' . $csvFilename, 'r');

        $csv = new Csv(Utils::streamFor($fh), true);
        $expected = include __DIR__ . '/_files/' . $expectedFilename;

        static::assertSame($expected, iterator_to_array($csv));
    }

    public function testFileWithVeryLongLine(): void
    {
        /**
         * The file has a single field with 30k characters on one line, which exceeds the {@see Csv::$rowSize }
         */
        $filename = __DIR__ . '/_files/long-line.csv';
        $fh = @fopen($filename, 'r');

        $csv = new Csv(Utils::streamFor($fh), true);

        // Get the headers without issue
        $expectedHeader = ['email', 'name', 'fieldA', 'fieldB'];
        static::assertEquals($expectedHeader, $csv->headers());

        // Line 1 without issue
        $expected = [
            'email' => 'test1@example.com',
            'name' => 'One',
            'fieldA' => 'Lorem ipsum dolor sit amet',
            'fieldB' => 'End1',
        ];
        static::assertSame($expected, $csv->current());

        // Line 2 cannot be read
        $this->expectException(\DomainException::class);
        // @todo The current exception is misleading, as the line is only partially read and misses the quote
        //       encapsulation, leaving the single long field containing commas broken into many fields
        $this->expectExceptionMessage('Row has more columns than headers');
        $csv->next();
        $csv->current();
    }
}
