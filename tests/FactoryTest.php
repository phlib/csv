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
            'name'  => 'Adam'
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
            'name'  => 'Adam'
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
}
