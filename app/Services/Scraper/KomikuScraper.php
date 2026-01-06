<?php

namespace App\Services\Scraper;

class KomikuScraper extends BaseScraper
{
    protected $baseUrl = 'https://komiku.id';

    public function fetchMangaList(int $pages = 1): array
    {
        // Dummy implementation - similar to Komikindo
        return [];
    }

    public function fetchMangaDetail(array $entry): array
    {
        return [];
    }

    public function fetchChapterPages(array $chapter): array
    {
        return [];
    }
}
