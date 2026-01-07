<?php

namespace App\Services\Scraper;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

abstract class BaseScraper
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => config('scraper.timeout', 30),
            'verify' => false,
        ]);
    }

    abstract public function fetchMangaList(int $pages = 1): array;
    abstract public function fetchMangaDetail(array $entry): array;
    abstract public function fetchChapterPages(array $chapter): array;

    protected function get(string $url, array $headers = []): string
    {
        $response = $this->client->get($url, [
            'headers' => array_merge([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ], $headers)
        ]);

        return $response->getBody()->getContents();
    }

    protected function cleanText(?string $text): string
    {
        if (!$text) return '';
        return trim(strip_tags($text));
    }

    protected function createSlug(string $title): string
    {
        return Str::slug($title);
    }

    protected function absoluteUrl(string $base, ?string $relative): ?string
    {
        if (!$relative) return null;
        
        if (str_starts_with($relative, 'http')) {
            return $relative;
        }
        
        return rtrim($base, '/') . '/' . ltrim($relative, '/');
    }

    protected function parseChapterNumber(string $text): ?string
    {
        if (preg_match('/(\d+(?:\.\d+)?)/', $text, $matches)) {
            return $matches[1];
        }
        return null;
    }

    protected function throttle(): void
    {
        $delayMs = (int) config('scraper.delay_ms', 500);
        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }
    }
}
