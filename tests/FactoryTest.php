<?php

declare(strict_types=1);

namespace Phlib\Csv\Tests;

use Phlib\Csv\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testCreateFromFile(): void
    {
        $filename = __DIR__ . '/_files/sample.csv';
        $csv = Factory::createFromFile($filename, true);

        $expectedResult = ['email', 'name'];
        static::assertEquals($expectedResult, $csv->headers());

        $expected = [
            'email' => 'aw@example.com',
            'name' => 'Adam',
        ];
        static::assertSame($expected, $csv->current());
    }

    public function testCreateFromFileNotExists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to open handle');

        $filename = __DIR__ . '/_files/does-not-exist.csv';
        Factory::createFromFile($filename);
    }

    public function testCreateFromZipFile(): void
    {
        $filename = __DIR__ . '/_files/sample.csv.zip';
        $csv = Factory::createFromZipFile($filename, true);

        $expectedResult = ['email', 'name'];
        static::assertEquals($expectedResult, $csv->headers());

        $expected = [
            'email' => 'aw@example.com',
            'name' => 'Adam',
        ];
        static::assertSame($expected, $csv->current());
    }

    public function testCreateFromZipFileNotExists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to open Zip file');

        $filename = __DIR__ . '/_files/does-not-exist.csv.zip';
        Factory::createFromZipFile($filename);
    }

    public function testCreateFromLargeFileIsMemoryEfficient(): void
    {
        if (getenv('SKIP_LARGE')) {
            static::markTestSkipped('Large test skipped; SKIP_LARGE is true');
        }

        $filename = __DIR__ . '/_files/large.csv';
        $fp = fopen($filename, 'w+b');
        $size = 100 * 1024 * 1024; // 100MiB file

        while ($size > 0) {
            $size -= fwrite($fp, random_bytes(8192));
        }
        fclose($fp);

        try {
            Factory::createFromFile($filename);
        } finally {
            unlink($filename);
            static::assertLessThan(20 * 1024 * 1024, memory_get_peak_usage()); // less than 20MiB of memory was used
        }
    }

    public function testCreateFromLargeZipFileIsMemoryEfficient(): void
    {
        if (getenv('SKIP_LARGE')) {
            static::markTestSkipped('Large test skipped; SKIP_LARGE is true');
        }

        $filename = __DIR__ . '/_files/large.csv';
        $fp = fopen($filename, 'w+b');
        $size = 100 * 1024 * 1024; // 100MiB file

        while ($size > 0) {
            $size -= fwrite($fp, random_bytes(8192));
        }
        fclose($fp);

        $zipName = __DIR__ . '/_files/large.csv.zip';
        $zip = new \ZipArchive();
        $zip->open($zipName, \ZipArchive::CREATE);
        $zip->addFile($filename);
        $zip->close();

        unlink($filename);

        try {
            Factory::createFromZipFile($zipName);
        } finally {
            unlink($zipName);
            static::assertLessThan(20 * 1024 * 1024, memory_get_peak_usage()); // less than 20MiB of memory was used
        }
    }
}
