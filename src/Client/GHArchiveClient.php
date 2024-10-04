<?php

declare(strict_types=1);

namespace App\Client;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GHArchiveClient
{
    private const URL = 'https://data.gharchive.org/%s-%s.json.gz';
    private const DESTINATION = '/tmp/%s-%s.json.gz';

    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    public function downloadEvents(string $date, string $hour, array $options = []): string
    {
        $url = \sprintf(self::URL, $date, $hour);
        $destination = \sprintf(self::DESTINATION, $date, $hour);

        try {
            $response = $this->httpClient->request(Request::METHOD_GET, $url, $options);

            $file = new \SplFileObject($destination, 'w');
            foreach ($this->httpClient->stream($response) as $chunk) {
                if ($chunk->isTimeout()) {
                    continue;
                }

                $file->fwrite($chunk->getContent());

                if ($chunk->isLast()) {
                    break;
                }
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Failed to download events: '.$e->getMessage(), 0, $e);
        } finally {
            $file = null;
        }

        return $destination;
    }
}
