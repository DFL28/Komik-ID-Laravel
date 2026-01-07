<?php

namespace App\Services\Scraper;

use simplehtmldom\HtmlDocument;
use Illuminate\Support\Carbon;

class KomikindoScraper extends BaseScraper
{
    protected $baseUrl = 'https://komikindo.ch';
    protected $listPath = '/daftar-manga/';

    public function fetchMangaList(int $pages = 1): array
    {
        $mangaList = [];
        $firstUrl = $this->baseUrl . $this->listPath;

        $html = $this->get($firstUrl);
        $mangaList = array_merge($mangaList, $this->parseMangaList($html));

        if ($pages <= 0) {
            $pages = $this->detectTotalPages($html);
        }

        for ($page = 2; $page <= $pages; $page++) {
            $url = "{$this->baseUrl}{$this->listPath}page/{$page}/";

            try {
                $html = $this->get($url);
                $mangaList = array_merge($mangaList, $this->parseMangaList($html));
            } catch (\Exception $e) {
                break;
            }

            $this->throttle();
        }

        $unique = [];
        foreach ($mangaList as $item) {
            $unique[$item['slug']] = $item;
        }

        return array_values($unique);
    }

    public function fetchMangaListPage(int $page, ?int &$totalPages = null): array
    {
        $url = $page <= 1
            ? $this->baseUrl . $this->listPath
            : "{$this->baseUrl}{$this->listPath}page/{$page}/";

        $html = $this->get($url);

        if ($page === 1) {
            $detected = $this->detectTotalPages($html);
            if ($totalPages !== null) {
                $totalPages = $detected;
            }
        }

        return $this->parseMangaList($html);
    }

    public function fetchMangaDetail(array $entry): array
    {
        $html = $this->get($entry['detailUrl']);
        $dom = new HtmlDocument($html);

        $title = $this->cleanText($dom->find('h1.entry-title', 0)?->plaintext ?? $entry['title']);
        $title = preg_replace('/^Komik\\s+/i', '', $title);

        $coverPath = $dom->find('.thumb img', 0)?->getAttribute('data-src')
            ?? $dom->find('.thumb img', 0)?->getAttribute('src');

        if (!$coverPath) {
            $coverPath = $this->extractJsonLdImage($html);
        }

        $description = $this->cleanText($dom->find('.entry-content.entry-content-single', 0)?->plaintext ?? '');
        if (!$description) {
            $description = $this->extractJsonLdDescription($html);
        }

        $genres = [];
        foreach ($dom->find('.genre-info a, .genxed a') as $genreLink) {
            $genres[] = $this->cleanText($genreLink->plaintext);
        }

        $info = $this->extractInfo($dom);
        $chapters = $this->extractChapters($dom);

        return [
            'title' => $title ?: $entry['title'],
            'slug' => $entry['slug'],
            'description' => $description,
            'cover_path' => $coverPath ? $this->absoluteUrl($this->baseUrl, $coverPath) : null,
            'genres' => $genres,
            'status' => $info['status'] ?? 'ongoing',
            'type' => $info['type'] ?? $entry['type'] ?? 'Manga',
            'author' => $info['author'] ?? null,
            'artist' => $info['artist'] ?? null,
            'alternative_title' => $info['alternative_title'] ?? null,
            'rating' => $info['rating'] ?? $entry['rating'] ?? 0,
            'content_type' => $entry['content_type'] ?? null,
            'chapters' => $chapters,
        ];
    }

    public function fetchChapterPages(array $chapter): array
    {
        $images = $this->fetchChapterImages($chapter['source_url']);
        return array_map(function ($url) {
            return [
                'url' => $url,
                'extension' => '.jpg',
            ];
        }, $images);
    }

    public function fetchChapterImages(string $chapterUrl): array
    {
        $html = $this->get($chapterUrl);
        $dom = new HtmlDocument($html);

        $images = [];
        foreach ($dom->find('#chimg-auh img, .reader-area img') as $img) {
            $imgUrl = $img->getAttribute('data-src');
            if (!$imgUrl) {
                $imgUrl = $img->getAttribute('data-lazy-src');
            }
            if (!$imgUrl) {
                $imgUrl = $img->getAttribute('data-original');
            }
            if (!$imgUrl) {
                $imgUrl = $img->getAttribute('src');
            }
            if ($imgUrl) {
                $images[] = $this->absoluteUrl($this->baseUrl, $imgUrl);
            }
        }

        return array_values(array_unique($images));
    }

    protected function parseMangaList(string $html): array
    {
        $dom = new HtmlDocument($html);
        $items = [];

        foreach ($dom->find('.listupd .animepost') as $post) {
            $link = $post->find('.animposx > a', 0) ?? $post->find('a', 0);
            if (!$link) {
                continue;
            }

            $detailUrl = $link->href ?? '';
            if (!str_contains($detailUrl, '/komik/')) {
                continue;
            }

            $title = $this->cleanText($post->find('.bigors .tt a', 0)?->plaintext ?? $link->title ?? $link->plaintext);
            $coverPath = $post->find('.animposx img', 0)?->getAttribute('data-src')
                ?? $post->find('.animposx img', 0)?->getAttribute('src');

            $slug = $this->slugFromUrl($detailUrl, $title);
            $type = $this->extractTypeFlag($post);
            $rating = $this->extractRating($post);
            $contentType = $post->find('.warnalabel', 0) ? 'color' : null;

            if ($title && $detailUrl && $slug) {
                $items[] = [
                    'title' => $title,
                    'detailUrl' => $detailUrl,
                    'slug' => $slug,
                    'cover_path' => $coverPath ? $this->absoluteUrl($this->baseUrl, $coverPath) : null,
                    'type' => $type,
                    'rating' => $rating,
                    'content_type' => $contentType,
                ];
            }
        }

        return $items;
    }

    protected function detectTotalPages(string $html): int
    {
        if (preg_match_all('/\\/daftar-manga\\/page\\/(\\d+)\\//', $html, $matches)) {
            return (int) max($matches[1]);
        }

        return 1;
    }

    protected function extractInfo(HtmlDocument $dom): array
    {
        $info = [
            'alternative_title' => null,
            'author' => null,
            'artist' => null,
            'status' => null,
            'type' => null,
            'rating' => null,
        ];

        foreach ($dom->find('.infox .spe span') as $span) {
            $labelNode = $span->find('b', 0);
            if (!$labelNode) {
                continue;
            }

            $label = strtolower(trim(rtrim($labelNode->plaintext, ':')));
            $value = trim(str_replace($labelNode->plaintext, '', $span->plaintext));

            if ($label === 'judul alternatif') {
                $info['alternative_title'] = $value ?: null;
            } elseif ($label === 'pengarang') {
                $info['author'] = $value ?: null;
            } elseif ($label === 'ilustrator') {
                $info['artist'] = $value ?: null;
            } elseif ($label === 'status') {
                $info['status'] = $this->normalizeStatus($value);
            } elseif ($label === 'jenis komik') {
                $info['type'] = $this->cleanText($span->find('a', 0)?->plaintext ?? $value);
            }
        }

        $ratingNode = $dom->find('.archiveanime-rating i', 0);
        if ($ratingNode) {
            $rating = floatval(str_replace(',', '.', $this->cleanText($ratingNode->plaintext)));
            $info['rating'] = $rating > 0 ? $rating : null;
        }

        return $info;
    }

    protected function extractChapters(HtmlDocument $dom): array
    {
        $chapters = [];

        foreach ($dom->find('#chapter_list li') as $item) {
            $link = $item->find('a', 0);
            if (!$link) {
                continue;
            }

            $chapterTitle = $this->cleanText($link->plaintext);
            $chapterUrl = $link->href ?? null;
            $number = $this->parseChapterNumber($chapterTitle);

            $dateText = $this->cleanText($item->find('.dt', 0)?->plaintext ?? '');
            $publishedAt = $this->parseDate($dateText);

            if ($number && $chapterUrl) {
                $chapters[] = [
                    'number' => $number,
                    'title' => $chapterTitle,
                    'source_url' => $chapterUrl,
                    'published_at' => $publishedAt,
                ];
            }
        }

        return $chapters;
    }

    protected function parseDate(string $text): ?Carbon
    {
        $text = strtolower(trim($text));
        if ($text === '') {
            return null;
        }

        if (str_contains($text, 'jam')) {
            $hours = $this->extractNumber($text);
            return now()->subHours($hours);
        }

        if (str_contains($text, 'hari')) {
            $days = $this->extractNumber($text);
            return now()->subDays($days);
        }

        if (str_contains($text, 'minggu')) {
            $weeks = $this->extractNumber($text);
            return now()->subWeeks($weeks);
        }

        if (str_contains($text, 'bulan')) {
            $months = $this->extractNumber($text);
            return now()->subMonths($months);
        }

        if (str_contains($text, 'tahun')) {
            $years = $this->extractNumber($text);
            return now()->subYears($years);
        }

        if (str_contains($text, 'kemarin')) {
            return now()->subDay();
        }

        $parsed = $this->parseIndonesianDate($text);
        return $parsed ?? null;
    }

    protected function parseIndonesianDate(string $text): ?Carbon
    {
        $months = [
            'januari' => '01',
            'februari' => '02',
            'maret' => '03',
            'april' => '04',
            'mei' => '05',
            'juni' => '06',
            'juli' => '07',
            'agustus' => '08',
            'september' => '09',
            'oktober' => '10',
            'november' => '11',
            'desember' => '12',
        ];

        foreach ($months as $name => $number) {
            if (str_contains($text, $name)) {
                $text = str_replace($name, $number, $text);
                break;
            }
        }

        if (preg_match('/(\\d{1,2})\\s+(\\d{2})\\s+(\\d{4})/', $text, $matches)) {
            return Carbon::createFromFormat('d m Y', "{$matches[1]} {$matches[2]} {$matches[3]}");
        }

        return null;
    }

    protected function extractNumber(string $text): int
    {
        if (preg_match('/(\\d+)/', $text, $matches)) {
            return (int) $matches[1];
        }

        return 1;
    }

    protected function normalizeStatus(string $value): string
    {
        $value = strtolower($value);
        if (str_contains($value, 'tamat') || str_contains($value, 'selesai') || str_contains($value, 'completed')) {
            return 'completed';
        }

        return 'ongoing';
    }

    protected function extractTypeFlag($post): ?string
    {
        $typeNode = $post->find('.typeflag', 0);
        if (!$typeNode) {
            return null;
        }

        $classes = preg_split('/\\s+/', $typeNode->class ?? '');
        foreach ($classes as $class) {
            if ($class && strtolower($class) !== 'typeflag') {
                return ucfirst(strtolower($class));
            }
        }

        return null;
    }

    protected function extractRating($post): float
    {
        $ratingNode = $post->find('.rating i', 0);
        if ($ratingNode) {
            return floatval(str_replace(',', '.', $this->cleanText($ratingNode->plaintext)));
        }

        return 0.0;
    }

    protected function slugFromUrl(string $url, string $title): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $path = trim($path, '/');
        if (str_starts_with($path, 'komik/')) {
            $slug = substr($path, strlen('komik/'));
            return $slug !== '' ? $slug : $this->createSlug($title);
        }

        return $this->createSlug($title);
    }

    protected function extractJsonLdImage(string $html): ?string
    {
        if (preg_match('/\"thumbnailUrl\"\\s*:\\s*\"([^\"]+)\"/i', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function extractJsonLdDescription(string $html): ?string
    {
        if (preg_match('/\"description\"\\s*:\\s*\"([^\"]+)\"/i', $html, $matches)) {
            return html_entity_decode($matches[1], ENT_QUOTES);
        }

        return null;
    }
}
