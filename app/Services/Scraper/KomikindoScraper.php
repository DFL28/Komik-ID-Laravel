<?php

namespace App\Services\Scraper;

use simplehtmldom\HtmlDocument;

class KomikindoScraper extends BaseScraper
{
    protected $baseUrl = 'https://komikindo.ch';

    public function fetchMangaList(int $pages = 1): array
    {
        $mangaList = [];
        
        for ($page = 1; $page <= $pages; $page++) {
            $url = "{$this->baseUrl}/komik-terbaru/page/{$page}/";
            
            try {
                $html = $this->get($url);
                $dom = new HtmlDocument($html);
                
                foreach ($dom->find('.bs .bsx a') as $link) {
                    $title = $this->cleanText($link->find('.tt', 0)?->plaintext ?? '');
                    $detailUrl = $link->href;
                    $slug = $this->createSlug($title);
                    
                    if ($title && $detailUrl) {
                        $mangaList[] = [
                            'title' => $title,
                            'detailUrl' => $detailUrl,
                            'slug' => $slug,
                        ];
                    }
                }
            } catch (\Exception $e) {
                break;
            }
        }
        
        return $mangaList;
    }

    public function fetchMangaDetail(array $entry): array
    {
        $html = $this->get($entry['detailUrl']);
        $dom = new HtmlDocument($html);
        
        $title = $this->cleanText($dom->find('.infox h1', 0)?->plaintext ?? $entry['title']);
        $description = $this->cleanText($dom->find('.entry-content', 0)?->plaintext ?? '');
        $coverPath = $dom->find('.thumb img', 0)?->getAttribute('data-src') ?? null;
        
        $genres = [];
        foreach ($dom->find('.genre-info a, .genxed a') as $genreLink) {
            $genres[] = $this->cleanText($genreLink->plaintext);
        }
        
        $chapters = [];
        foreach ($dom->find('#chapter_list a, .eplister a') as $chapterLink) {
            $chapterTitle = $this->cleanText($chapterLink->plaintext);
            $chapterUrl = $chapterLink->href;
            $number = $this->parseChapterNumber($chapterTitle);
            
            if ($number && $chapterUrl) {
                $chapters[] = [
                    'number' => $number,
                    'title' => $chapterTitle,
                    'source_url' => $chapterUrl,
                ];
            }
        }
        
        return [
            'title' => $title,
            'slug' => $entry['slug'],
            'description' => $description,
            'cover_path' => $coverPath,
            'genres' => $genres,
            'status' => 'ongoing',
            'type' => 'Manga',
            'chapters' => $chapters,
        ];
    }

    public function fetchChapterPages(array $chapter): array
    {
        $html = $this->get($chapter['source_url']);
        $dom = new HtmlDocument($html);
        
        $pages = [];
        foreach ($dom->find('#chimg-auh img, .reader-area img') as $img) {
            $imgUrl = $img->getAttribute('data-src') ?? $img->src;
            if ($imgUrl) {
                $pages[] = [
                    'url' => $this->absoluteUrl($this->baseUrl, $imgUrl),
                    'extension' => '.jpg',
                ];
            }
        }
        
        return $pages;
    }
}
