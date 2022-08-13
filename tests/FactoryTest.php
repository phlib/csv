<?php

declare(strict_types=1);

namespace Phlib\Csv\Tests;

use Phlib\Csv\Factory;
use PHPUnit\Framework\TestCase;

class FactoryTest extends TestCase
{
    public function testCreateFromFile()
    {
        $filename = __DIR__ . '/_files/sample.csv';
        $csv = Factory::createFromFile($filename, true);

        $expectedResult = ['email', 'name'];
        $this->assertEquals($expectedResult, $csv->headers());

        $expected = [
            'email' => 'aw@example.com',
            'name' => 'Adam',
        ];
        $this->assertSame($expected, $csv->current());
    }

    public function testCreateFromFileNotExists()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to open handle');

        $filename = __DIR__ . '/_files/does-not-exist.csv';
        Factory::createFromFile($filename);
    }

    public function testCreateFromZipFile()
    {
        $filename = __DIR__ . '/_files/sample.csv.zip';
        $csv = Factory::createFromZipFile($filename, true);

        $expectedResult = ['email', 'name'];
        $this->assertEquals($expectedResult, $csv->headers());

        $expected = [
            'email' => 'aw@example.com',
            'name' => 'Adam',
        ];
        $this->assertSame($expected, $csv->current());
    }

    public function testCreateFromZipFileNotExists()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to open Zip file');

        $filename = __DIR__ . '/_files/does-not-exist.csv.zip';
        Factory::createFromZipFile($filename);
    }

    public function testCreateFromLargeFileIsMemoryEfficient()
    {
        if (isset($GLOBALS['SKIP_LARGE'])) {
            $this->markTestSkipped('Large test skipped; SKIP_LARGE is true');
            return;
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
            $this->assertLessThan(20 * 1024 * 1024, memory_get_peak_usage()); // less than 20MiB of memory was used
        }
    }

    public function testCreateFromLargeZipFileIsMemoryEfficient()
    {
        if (isset($GLOBALS['SKIP_LARGE'])) {
            $this->markTestSkipped('Large test skipped; SKIP_LARGE is true');
            return;
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
            $this->assertLessThan(20 * 1024 * 1024, memory_get_peak_usage()); // less than 20MiB of memory was used
        }
    }
}
