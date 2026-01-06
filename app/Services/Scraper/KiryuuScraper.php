<?php

namespace App\Services\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KiryuuScraper
{
    protected $baseUrl = 'https://kiryuu03.com';

    /**
     * Fetch list of manga from source
     */
    public function fetchMangaList(int $uptoPage = 1): array
    {
        $mangaList = [];

        for ($page = 1; $page <= $uptoPage; $page++) {
            $url = $page === 1 ? $this->baseUrl . '/manga/' : $this->baseUrl . '/manga/?page=' . $page;
            
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])->get($url);

                if ($response->successful()) {
                    $html = $response->body();
                    $mangaList = array_merge($mangaList, $this->extractMangaList($html));
                }
            } catch (\Exception $e) {
                // Log error but continue
                continue;
            }
            
            sleep(1); // Be polite
        }

        return $mangaList;
    }

    /**
     * Fetch detailed info of a manga using Puppeteer
     */
    public function fetchMangaDetail(array $mangaEntry): array
    {
        $url = $mangaEntry['url'];
        
        try {
            // Log::info("Using Puppeteer for detail: $url");
            $data = $this->fetchWithPuppeteer($url, 'detail');
            
            if ($data) {
                $mangaEntry['description'] = $data['description'] ?? $mangaEntry['description'] ?? '';
                // Fallback title/cover if empty
                if (!empty($data['title'])) $mangaEntry['title'] = $data['title'];
                if (!empty($data['cover'])) $mangaEntry['cover_path'] = $data['cover']; // Might want to download this
                
                $mangaEntry['genres'] = $data['genres'] ?? [];
                
                // Chapters
                if (!empty($data['chapters'])) {
                    $mangaEntry['chapters'] = $data['chapters']; // Already parsed in JS
                    
                    // Add date (now) as fallback
                    foreach ($mangaEntry['chapters'] as &$ch) {
                        $ch['published_at'] = now();
                    }
                }
            }
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error("Puppeteer Failed: " . $e->getMessage());
        }

        return $mangaEntry;
    }

    /**
     * Fetch all image URLs for a specific chapter using Puppeteer
     */
    public function fetchChapterImages(string $chapterUrl): array
    {
        try {
            $data = $this->fetchWithPuppeteer($chapterUrl, 'chapter');
            return $data['images'] ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Helper to run node script
     */
    protected function fetchWithPuppeteer($url, $mode) {
        $script = base_path('scraper.js');
        // Escape args
        $url = escapeshellarg($url);
        $mode = escapeshellarg($mode);
        
        // Use full path to node to be safe in shell_exec
        $nodePath = '"C:\\Program Files\\nodejs\\node.exe"';
        $cmd = "$nodePath \"$script\" $url $mode 2>&1";
        
        $output = shell_exec($cmd);
        
        // Try to find JSON part (ignore logs before it)
        // Check if output contains JSON start
        $jsonStart = strpos($output, '{');
        if ($jsonStart !== false) {
             $jsonStr = substr($output, $jsonStart);
             $data = json_decode($jsonStr, true);
             if ($data) return $data;
        }
        
        // If we talk here, failed
        \Illuminate\Support\Facades\Log::error("Puppeteer Execution Failed. Cmd: $cmd. Output: $output");
        
        return null;
    }

    // --- Private Extractors ---

    protected function extractMangaList(string $html): array
    {
        $list = [];
        
        // Kiryuu Tailwind Structure
        // <a href="URL"> ... <img src="IMG"> ... </a>
        // We look for links containing /manga/ to be safe
        preg_match_all('/<a[^>]*href="(https:\/\/kiryuu03\.com\/manga\/[^"]+)"[^>]*>.*?<img[^>]*src="([^"]+)"[^>]*>/is', $html, $matches, PREG_SET_ORDER);
        
        \Illuminate\Support\Facades\Log::info("KiryuuScraper: Scraped HTML length: " . strlen($html) . ". Matches found: " . count($matches));

        if (empty($matches)) {
            // Fallback for different layouts (e.g. homepage latest update might be different)
             preg_match_all('/<div class="bs">.*?<a href="([^"]+)" title="([^"]+)">.*?<img src="([^"]+)".*?<\/div>/is', $html, $matches, PREG_SET_ORDER);
        }

        foreach ($matches as $match) {
            $url = $match[1];
            // Cover is usually in group 2 for the Tailwind regex
            $cover = $match[2];
            
            // Try to extract title from URL basename
            $slug = basename(trim($url, '/'));
            $title = ucwords(str_replace('-', ' ', $slug));
            
            // If regex was the "bs" fallback, we might have title in group 2 and cover in 3
            if (count($match) >= 4) {
                 $url = $match[1];
                 $title = html_entity_decode($match[2]);
                 $cover = $match[3];
                 $slug = basename(trim($url, '/'));
            }

            // Clean cover URL
            $cover = preg_replace('/\?.*$/', '', $cover);

            // Avoid duplicates in the same page scrape
            $list[$slug] = [
                'title' => $title,
                'slug' => $slug,
                'url' => $url,
                'cover_path' => $cover,
                'genres' => [],
                'chapters' => [],
                'description' => '',
                'type' => 'manga',
                'status' => 'ongoing',
                'rating' => 0,
            ];
        }

        return array_values($list);
    }

    protected function extractChapters(string $html): array
    {
        // Kiryuu usually uses #chapterlist UL LI
        $chapters = [];
        
        // Find container
        if (preg_match('/<div id="chapterlist"[^>]*>(.*?)<\/div>/is', $html, $container)) {
            $listHtml = $container[1];
            
            // Match items: data-num="X", href="URL", .chapternum "Chapter X"
            preg_match_all('/<li[^>]*data-num="([^"]+)"[^>]*>.*?<a href="([^"]+)"[^>]*>.*?<span class="chapternum">(.*?)<\/span>.*?<span class="chapterdate">(.*?)<\/span>/is', $listHtml, $matches, PREG_SET_ORDER);
            
            foreach ($matches as $m) {
                $num = $m[1];
                $url = $m[2];
                $titleRaw = strip_tags($m[3]);
                $dateRaw = strip_tags($m[4]);
                
                // Parse number safely
                $numVal = (float) $num;
                
                $chapters[] = [
                    'number' => $numVal,
                    'title' => trim($titleRaw),
                    'source_url' => $url,
                    'published_at' => $this->parseDate($dateRaw),
                ];
            }
        }
        
        // Remove duplicates (by number)
        $unique = [];
        foreach ($chapters as $ch) {
            $unique[(string)$ch['number']] = $ch;
        }
        
        // Sort DESC
        $final = array_values($unique);
        usort($final, fn($a, $b) => $b['number'] <=> $a['number']);
        
        return array_reverse($final); // Return ASC for saving
    }
    
    protected function extractChapterImages(string $html): array
    {
        // Kiryuu reader: #readerarea img
        if (preg_match('/<div id="readerarea"[^>]*>(.*?)<\/div>/is', $html, $area)) {
            $content = $area[1];
            preg_match_all('/<img[^>]+src="([^"]+)"/is', $content, $matches);
            
            $images = [];
            foreach ($matches[1] as $src) {
                // Filter out irrelevant images/ads if any
                if (!str_contains($src, 'data:image')) {
                     // Check if it's HTTPS
                     if (strpos($src, '//') === 0) {
                         $src = 'https:' . $src;
                     }
                     $images[] = trim($src);
                }
            }
            return $images;
        }
        
        // Fallback: ts_reader script
        if (preg_match('/ts_reader\.run\((.*?)\)/is', $html, $m)) {
            $json = json_decode($m[1], true);
            if (isset($json['sources'][0]['images'])) {
                return $json['sources'][0]['images'];
            }
        }
        
        return [];
    }

    protected function parseDate($str) {
        // Simplified date parser
        return now(); 
    }
}
