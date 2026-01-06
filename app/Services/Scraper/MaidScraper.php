<?php

namespace App\Services\Scraper;

class MaidScraper extends BaseScraper
{
    protected $baseUrl = 'https://maid.my.id';

    public function fetchMangaList(int $pages = 1): array
    {
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
