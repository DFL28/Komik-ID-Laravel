<?php

namespace App\Services\Scraper;

class WestmangaScraper extends BaseScraper
{
    protected $baseUrl = 'https://westmanga.info';

    public function fetchMangaList(int $pages = 1): array
    {
        // Stub - similar pattern to Komikindo
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
