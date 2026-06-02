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
}
