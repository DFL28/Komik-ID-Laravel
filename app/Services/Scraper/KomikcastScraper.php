<?php

namespace App\Services\Scraper;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KomikcastScraper
{
    protected $baseUrl = 'https://komikcast.co.id';
    
    public function fetchMangaList(int $pages = 1): array
    {
        $mangaList = [];
        
        for ($page = 1; $page <= $pages; $page++) {
            $url = $page === 1 ? $this->baseUrl  : "{$this->baseUrl}/page/{$page}/";
            
            try {
                Log::info("Fetching page", ['url' => $url]);
                
                $response = Http::timeout(30)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'])
                    ->get($url);
                
                if (!$response->successful()) {
                    Log::error("HTTP request failed", ['status' => $response->status()]);
                    continue;
                }
                
                $html = $response->body();
                Log::info("Got HTML", ['size' => strlen($html)]);
                
                // Parse manga items - Komikcast uses <div class="bsx">
                preg_match_all(
                    '/<div class="bsx">.*?<a href="([^"]+)"[^>]*>.*?<img[^>]*src="([^"]+)"[^>]*title="([^"]+)"/s',
                    $html,
                    $matches,
                    PREG_SET_ORDER
                );
                
                Log::info("Found manga items", ['count' => count($matches)]);
                
                foreach ($matches as $match) {
                    $itemUrl = $match[1];
                    
                    // Only manga detail pages (komikcast uses /manga/ not /komik/)
                    if (!str_contains($itemUrl, '/manga/') || str_contains($itemUrl, '/page/') || str_contains($itemUrl, '?order')) {
                        continue;
                    }
                    
                    $mangaList[] = [
                        'url' => $itemUrl,
                        'cover' => $match[2],
                        'title' => html_entity_decode($match[3]),
                    ];
                }
                
                if ($page < $pages) {
                    sleep(1);
                }
                
            } catch (\Exception $e) {
                Log::error("Failed to fetch page", ['page' => $page, 'error' => $e->getMessage()]);
                continue;
            }
        }
        
        $mangaList = array_unique($mangaList, SORT_REGULAR);
        Log::info("Total unique manga", ['count' => count($mangaList)]);
        
        return $mangaList;
    }
    
    public function fetchMangaDetail(array $entry): array
    {
        try {
            Log::info("Fetching manga detail", ['title' => $entry['title']]);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Referer' => 'https://komikcast.co.id/'
                ])
                ->get($entry['url']);
            
            if (!$response->successful()) {
                throw new \Exception("Failed to fetch detail");
            }
            
            $html = $response->body();
            
            return [
                'title' => $entry['title'],
                'slug' => $this->generateSlug($entry['url']),
                'cover_path' => $entry['cover'],
                'alternative_title' => $this->extractAlternativeTitle($html),
                'description' => $this->extractDescription($html),
                'author' => $this->extractAuthor($html),
                'status' => $this->extractStatus($html),
                'type' => $this->extractType($html),
                'genres' => $this->extractGenres($html),
                'rating' => $this->extractRating($html),
                'country' => 'ID',
                'content_type' => 'Manhwa',
                'chapters' => $this->extractChapters($html),
            ];
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch detail", ['title' => $entry['title'], 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    protected function generateSlug(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $slug = trim($path, '/');
        $slug = str_replace('komik/', '', $slug);
        return Str::slug($slug);
    }
    
    protected function extractAlternativeTitle(string $html): ?string
    {
        if (preg_match('/<span[^>]*class="[^"]*alter[^"]*"[^>]*>([^<]+)<\/span>/i', $html, $match)) {
            return trim($match[1]);
        }
        return null;
    }
    
    protected function extractDescription(string $html): ?string
    {
        $patterns = [
            '/<div[^>]*itemprop="description"[^>]*>(.*?)<\/div>/is',
            '/<div[^>]*class="[^"]*entry-content[^"]*"[^>]*>(.*?)<\/div>/is',
            '/<div[^>]*class="[^"]*sinopsis[^"]*"[^>]*>(.*?)<\/div>/is',
            '/Sinopsis.*?<p>(.*?)<\/p>/is'
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $html, $match)) {
                $desc = strip_tags($match[1]);
                $desc = preg_replace('/\s+/', ' ', $desc);
                return trim($desc);
            }
        }
        
        return null;
    }
    
    protected function extractAuthor(string $html): ?string
    {
        if (preg_match('/>Author.*?<a[^>]*>([^<]+)<\/a>/is', $html, $match)) {
            return trim($match[1]);
        }
        return 'Unknown';
    }
    
    protected function extractStatus(string $html): string
    {
        if (preg_match('/>Status.*?<span[^>]*>([^<]+)<\/span>/is', $html, $match)) {
            $status = strtolower(trim($match[1]));
            return str_contains($status, 'ongoing') ? 'ongoing' : 'completed';
        }
        return 'ongoing';
    }
    
    protected function extractType(string $html): string
    {
        if (preg_match('/>Type.*?<a[^>]*>([^<]+)<\/a>/is', $html, $match)) {
            return trim($match[1]);
        }
        return 'Manga';
    }
    
    protected function extractGenres(string $html): array
    {
        $genres = [];
        
        if (preg_match('/>Genres.*?<span[^>]*>(.*?)<\/span>/is', $html, $genreBlock)) {
            preg_match_all('/<a[^>]*>([^<]+)<\/a>/i', $genreBlock[1], $matches);
            foreach ($matches[1] as $genre) {
                $genres[] = trim($genre);
            }
        }
        
        return $genres;
    }
    
    protected function extractRating(string $html): float
    {
        if (preg_match('/>Rating.*?([0-9.]+)/is', $html, $match)) {
            return (float) $match[1];
        }
        return 0.0;
    }
    
    protected function extractChapters(string $html): array
    {
        $chapters = [];
        
        // Find the chapter list container first to avoid scraping sidebar/footer links
        // Komikcast usually uses <div id="chapterlist"> or <ul class="clstyle">
        $listContent = '';
        if (preg_match('/<div[^>]*id="chapterlist"[^>]*>(.*?)<\/div>/is', $html, $match)) {
            $listContent = $match[1];
        } elseif (preg_match('/<ul[^>]*class="clstyle"[^>]*>(.*?)<\/ul>/is', $html, $match)) {
            $listContent = $match[1];
        } else {
            // Fallback: search for any UL that contains chapter links
            if (preg_match('/<ul[^>]*>[\s\S]*?chapter-[\d]+[\s\S]*?<\/ul>/i', $html, $match)) {
                $listContent = $match[0];
            } else {
                return []; // No chapter list found
            }
        }
        
        // Now split by <li> to process each item
        preg_match_all('/<li[^>]*>(.*?)<\/li>/is', $listContent, $liMatches);
        
        foreach ($liMatches[1] as $liContent) {
            // Try to find link
            if (!preg_match('/<a[^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/is', $liContent, $linkMatch)) {
                continue;
            }
            
            $url = $linkMatch[1];
            $title = strip_tags($linkMatch[2]);
            $num = 0;
            
            // Priority 1: Get number from data-num attribute in the list item tag (checking original li strings)
            // But we inside loop of content. Let's start clean.
            
            // Extract number from URL (Most reliable usually)
            if (preg_match('/chapter-([\d\.]+)/i', $url, $numMatch)) {
                $num = (float) $numMatch[1];
            } else {
                 // Try from title text
                 if (preg_match('/chapter\s+([\d\.]+)/i', $title, $numMatch)) {
                     $num = (float) $numMatch[1];
                 }
            }
            
            if ($num > 0) {
                 $chapters[] = [
                    'number' => $num,
                    'title' => trim($title),
                    'source_url' => $url,
                    'published_at' => now(), // Date extraction is complex, skipping for speed
                ];
            }
        }
        
        // Remove duplicates and sort
        $uniqueChapters = [];
        foreach ($chapters as $ch) {
            // Use string key to preserve decimals like "8.5"
            $key = (string)$ch['number'];
            if (!isset($uniqueChapters[$key])) {
                $uniqueChapters[$key] = $ch;
            }
        }
        
        $chapters = array_values($uniqueChapters);
        
        // Sort by chapter number descending
        usort($chapters, function($a, $b) {
            return $b['number'] <=> $a['number'];
        });
        
        return array_reverse($chapters); // Return ascending
    }
    
    public function fetchChapterImages(string $chapterUrl): array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Referer' => 'https://komikcast.co.id/'
                ])
                ->get($chapterUrl);
            
            if (!$response->successful()) {
                return [];
            }
            
            $html = $response->body();
            $images = [];
            
            // Method 1: Try extracting from ts_reader JS object (most reliable for Komikcast/Madara themes)
            if (preg_match('/ts_reader\.run\((.*?)\);/s', $html, $match)) {
                $jsonStr = $match[1];
                $data = json_decode($jsonStr, true);
                
                if (isset($data['sources']) && !empty($data['sources'])) {
                    // Usually the first source is the default one
                    $images = $data['sources'][0]['images'];
                }
            }
            
            // Method 2: Fallback to Regex extraction if Method 1 specific JSON isn't found
            if (empty($images)) {
                 if (preg_match_all('/<img[^>]*class="[^"]*ts-main-image[^"]*"[^>]*(?:src|data-src)="([^"]+)"/i', $html, $matches)) {
                    $images = array_unique($matches[1]);
                }
            }

            // Clean up URLs
            foreach ($images as $key => $url) {
                // Ensure URL is valid and fix common JSON encoding artifacts if any
                $images[$key] = stripslashes(trim($url));
            }
            
            return array_values($images);
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch chapter images", ['error' => $e->getMessage()]);
            return [];
        }
    }
}
