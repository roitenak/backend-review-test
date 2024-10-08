<?php

declare(strict_types=1);

namespace App\Tests\Unit\Client;

use App\Client\GHArchiveClient;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Component\HttpFoundation\Request;

class GHArchiveClientTest extends TestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private GHArchiveClient $ghArchiveClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->ghArchiveClient = new GHArchiveClient($this->httpClient);
    }

    public function testDownloadEvents(): void
    {
        $date = '2023-10-01';
        $hour = '00';
        $options = [];
        $url = \sprintf('https://data.gharchive.org/%s-%s.json.gz', $date, $hour);
        $destination = \sprintf('/tmp/%s-%s.json.gz', $date, $hour);

        $response = $this->createMock(ResponseInterface::class);

        $chunk = $this->createMock(ChunkInterface::class);
        $timeoutChunk = $this->createMock(ChunkInterface::class);
        $lastChunk = $this->createMock(ChunkInterface::class);

        $this->httpClient
            ->expects($this->once())
            ->method('request')
            ->with(Request::METHOD_GET, $url, $options)
            ->willReturn($response);

            $this->httpClient
            ->expects($this->once())
            ->method('stream')
            ->with($response)
            ->willReturn(new ResponseStream($this->createGenerator([$chunk, $timeoutChunk, $lastChunk])));

        $chunk
            ->expects($this->once())
            ->method('isLast')
            ->willReturn(false);

        $chunk
            ->expects($this->once())
            ->method('isTimeout')
            ->willReturn(false);

        $chunk
            ->expects($this->once())
            ->method('getContent')
            ->willReturn("{\"key\":\"test content\"}");

        $timeoutChunk
            ->expects($this->once())
            ->method('isTimeout')
            ->willReturn(true);

        $lastChunk
            ->expects($this->once())
            ->method('isTimeout')
            ->willReturn(false);

        $lastChunk
            ->expects($this->once())
            ->method('isLast')
            ->willReturn(true);

        $lastChunk
            ->expects($this->never())
            ->method('getContent');


        $result = $this->ghArchiveClient->downloadEvents($date, $hour, $options);

        $this->assertSame($destination, $result);
        $this->assertFileExists($destination);
        $this->assertStringEqualsFile($destination, "{\"key\":\"test content\"}");

        unlink($destination);
    }

    public function testException(): void
    {
        $date = '2023-10-01';
        $hour = '00';
        $options = [];

        $this->httpClient
            ->expects($this->once())
            ->method('stream')
            ->willThrowException(new \Exception('Failed to download events'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to download events');

        $this->ghArchiveClient->downloadEvents($date, $hour, $options);
    }

    private function createGenerator(array $chunks): \Generator
    {
        foreach ($chunks as $chunk) {
            yield $chunk;
        }
    }
}
