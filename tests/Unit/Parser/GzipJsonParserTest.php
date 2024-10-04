<?php

declare(strict_types=1);

namespace App\Tests\Unit\Parser;

use App\Parser\GzipJsonParser;
use PHPUnit\Framework\TestCase;

class GzipJsonParserTest extends TestCase
{
    private GzipJsonParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new GzipJsonParser();
    }

    public function testParseValidGzipFile()
    {
        $sourceFile = __DIR__ . '/fixtures/valid.json.gz';

        $lines = iterator_to_array($this->parser->parse($sourceFile));

        $this->assertNotEmpty($lines);
        $this->assertIsArray($lines);
        $this->assertEquals('{"key":"value"}', $lines[0]);
    }

    public function testParseInvalidGzipFile()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Gzip file could not be opened');

        $sourceFile = __DIR__ . '/fixtures/invalid.json.gz';

        iterator_to_array($this->parser->parse($sourceFile));
    }

    public function testParseEmptyGzipFile()
    {
        $sourceFile = __DIR__ . '/fixtures/empty.json.gz';

        $lines = iterator_to_array($this->parser->parse($sourceFile));

        $this->assertEmpty($lines);
    }
}
