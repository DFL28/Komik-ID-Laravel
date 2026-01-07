<?php

namespace App\Services;

use App\Services\Scraper\KomikindoScraper;
use App\Services\ImageService;
use App\Models\Manga;
use App\Models\Chapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScraperService
{
    protected $scraper;
    protected $imageService;
    protected $aiService;

    protected $logFile;

    public function __construct(ImageService $imageService, AICategorizationService $aiService)
    {
        // Use Komikindo as the only source
        $this->scraper = new KomikindoScraper();
        $this->imageService = $imageService;
        $this->aiService = $aiService;
        $this->logFile = storage_path('logs/scraper.log');
        
        // Reset log file on new instantiation if needed, or just append
        // file_put_contents($this->logFile, ""); 
    }

    protected function log($message, $type = 'INFO')
    {
        $timestamp = date('Y-m-d H:i:s');
        $formatted = "[$timestamp] $type: $message" . PHP_EOL;
        file_put_contents($this->logFile, $formatted, FILE_APPEND);
        // Also log to Laravel default
        Log::info("SCRAPER: $message");
    }

    public function scrape(int $pages = 1, bool $downloadImages = false, bool $reset = false): array
    {
        // Clear log at start of new scrape session
        $date = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$date] INFO: Starting new scrape session...\n");

        $result = ['count' => 0, 'manga' => [], 'errors' => []];

        try {
            $pageLabel = $pages <= 0 ? 'auto' : (string) $pages;
            $this->log("Starting Komikindo Scrape (Pages: {$pageLabel}, Download Images: " . ($downloadImages ? 'Yes' : 'No') . ", Reset: " . ($reset ? 'Yes' : 'No') . ")");

            if ($reset) {
                $this->clearMangaData();
            }

            $consecutiveEmptyChapters = 0;
            $processed = 0;
            $seenSlugs = [];
            $batchSize = max(1, (int) config('scraper.batch_pages', 50));
            $batchCooldown = max(0, (int) config('scraper.batch_cooldown_seconds', 5));

            if ($pages <= 0) {
                $page = 1;
                $totalPages = 1;
                $batchStart = 1;

                while ($page <= $totalPages) {
                    $this->log("Fetching list page {$page}...");
                    try {
                        $pageItems = $this->scraper->fetchMangaListPage($page, $totalPages);
                    } catch (\Exception $e) {
                        $this->log("Failed to fetch list page {$page}: " . $e->getMessage(), 'ERROR');
                        break;
                    }

                    if ($page === 1) {
                        $this->log("Detected {$totalPages} list pages");
                        $this->log("Batch mode: {$batchSize} pages per batch");
                    }

                    $this->log("Fetched list page {$page}/{$totalPages} (" . count($pageItems) . " items)");
                    $pageItems = $this->dedupeEntries($pageItems, $seenSlugs);
                    $this->processMangaEntries($pageItems, $result, $processed, $consecutiveEmptyChapters, $downloadImages);

                    if ($batchSize > 0 && ($page % $batchSize === 0 || $page === $totalPages)) {
                        $batchEnd = $page;
                        $this->checkpointBatch($batchStart, $batchEnd, $batchCooldown);
                        $batchStart = $page + 1;
                    }

                    $page++;
                    $this->delay();
                }
            } else {
                $mangaList = $this->scraper->fetchMangaList($pages);
                $this->log("Found " . count($mangaList) . " manga items to process");
                $mangaList = $this->dedupeEntries($mangaList, $seenSlugs);
                $this->processMangaEntries($mangaList, $result, $processed, $consecutiveEmptyChapters, $downloadImages);
            }
            
            $this->log("Scrape session completed. Total: " . $result['count']);
            
        } catch (\Exception $e) {
            $this->log("FATAL ERROR: " . $e->getMessage(), 'ERROR');
            throw $e;
        }

        return $result;
    }

    public function clearMangaDataManual(): void
    {
        $this->clearMangaData();
    }

    protected function checkpointBatch(int $start, int $end, int $cooldownSeconds): void
    {
        $this->log("Batch checkpoint saved (pages {$start}-{$end}).");

        $checkpoint = [
            'start_page' => $start,
            'end_page' => $end,
            'timestamp' => date('Y-m-d H:i:s'),
        ];

        $checkpointPath = storage_path('app/scraper_checkpoint.json');
        @file_put_contents($checkpointPath, json_encode($checkpoint));

        if ($cooldownSeconds > 0) {
            $this->log("Cooldown {$cooldownSeconds}s before next batch...");
            sleep($cooldownSeconds);
        }

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }

    protected function processMangaEntries(array $entries, array &$result, int &$processed, int &$consecutiveEmptyChapters, bool $downloadImages): void
    {
        foreach ($entries as $entry) {
            try {
                $processed++;
                $this->log("Processing [{$processed}]: " . $entry['title']);

                $mangaData = $this->scraper->fetchMangaDetail($entry);

                // Verify data completeness
                if (empty($mangaData['chapters'])) {
                    $this->log("WARNING: No chapters found for " . $entry['title'], 'WARN');
                    $consecutiveEmptyChapters++;
                } else {
                    $this->log("Found " . count($mangaData['chapters']) . " chapters");
                    $consecutiveEmptyChapters = 0; // Reset counter on success
                }

                // CIRCUIT BREAKER: Stop if 5 consecutive mangas imply we are blocked
                if ($consecutiveEmptyChapters >= 5) {
                    throw new \Exception("Aborting scrape: Too many consecutive failures (5). You might be rate-limited or blocked by the source. Please try again later.");
                }

                // Always cache cover locally when possible
                if (!empty($mangaData['cover_path'])) {
                    $existingCover = Manga::where('slug', $mangaData['slug'])->value('cover_path');
                    if ($existingCover && !str_starts_with($existingCover, 'http')) {
                        $mangaData['cover_path'] = $existingCover;
                    } elseif (str_starts_with($mangaData['cover_path'], 'http')) {
                        $this->log("Caching cover...");
                        $localCoverPath = $this->imageService->downloadCover(
                            $mangaData['cover_path'],
                            $mangaData['slug']
                        );
                        if ($localCoverPath) {
                            $mangaData['cover_path'] = $localCoverPath;
                        }
                    }
                }

                $manga = $this->saveManga($mangaData);

                if ($manga && !empty($mangaData['chapters'])) {
                    $this->saveChapters($manga, $mangaData['chapters']);
                    // Only log success if we actually got content
                    $this->log("Success: " . $manga->title);
                    $result['count']++;
                    $result['manga'][] = $manga->title;
                } else {
                    $this->log("Skipped saving content for " . $entry['title'] . " due to missing data.", 'WARN');
                }

                $this->delay();

            } catch (\Exception $e) {
                $error = "Failed to scrape {$entry['title']}: " . $e->getMessage();
                $this->log($error, 'ERROR');
                $result['errors'][] = $error;

                // If fatal abortion, rethrow
                if (str_contains($e->getMessage(), 'Aborting scrape')) {
                    throw $e;
                }
                continue;
            }
        }
    }

    protected function dedupeEntries(array $entries, array &$seenSlugs): array
    {
        $unique = [];

        foreach ($entries as $entry) {
            $slug = $entry['slug'] ?? null;
            if (!$slug || isset($seenSlugs[$slug])) {
                continue;
            }

            $seenSlugs[$slug] = true;
            $unique[] = $entry;
        }

        return $unique;
    }

    protected function delay(): void
    {
        $delayMs = (int) config('scraper.delay_ms', 500);
        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }
    }

    protected function clearMangaData(): void
    {
        $this->log('Clearing existing manga data...');

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        DB::table('reading_history')->truncate();
        DB::table('comments')->truncate();
        DB::table('bookmarks')->truncate();
        DB::table('chapters')->truncate();
        DB::table('manga')->truncate();

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }

        $this->log('Existing manga data cleared.');
    }

    /**
     * Save or update manga
     */
    protected function saveManga(array $data): Manga
    {
        // AI Categorization: If genres are empty or minimal, use AI
        $genres = $data['genres'] ?? [];
        
        if (empty($genres) || (is_array($genres) && count($genres) < 2)) {
            $this->log("🤖 AI Categorization for: {$data['title']}");
            
            try {
                $aiGenres = $this->aiService->categorizeManga(
                    $data['title'],
                    $data['description'] ?? null
                );
                
                if (!empty($aiGenres)) {
                    $genres = array_merge(is_array($genres) ? $genres : [], $aiGenres);
                    $this->log("✓ AI found genres: " . implode(', ', $aiGenres));
                }
            } catch (\Exception $e) {
                $this->log("⚠ AI categorization failed: " . $e->getMessage(), 'WARN');
            }
        }
        
        return Manga::updateOrCreate(
            ['slug' => $data['slug']],
            [
                'title' => $data['title'],
                'alternative_title' => $data['alternative_title'] ?? null,
                'description' => $data['description'] ?? null,
                'cover_path' => $data['cover_path'] ?? null,
                'author' => $data['author'] ?? null,
                'artist' => $data['artist'] ?? null,
                'serialization' => $data['serialization'] ?? null,
                'status' => $data['status'] ?? 'ongoing',
                'type' => $data['type'] ?? 'Manga',
                'genres' => is_array($genres) ? implode(',', array_unique($genres)) : $genres,
                'rating' => $data['rating'] ?? 0,
                'country' => $data['country'] ?? 'ID',
                'content_type' => $data['content_type'] ?? 'Manhwa',
            ]
        );
    }

    /**
     * Save chapters for manga
     */
    protected function saveChapters(Manga $manga, array $chapters): void
    {
        $saved = 0;
        
        // Chunk chapters to avoid long locks
        $chunks = array_chunk($chapters, 50);
        
        foreach ($chunks as $chunk) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($chunk, $manga, &$saved) {
                    foreach ($chunk as $chapterData) {
                        try {
                            Chapter::updateOrCreate(
                                [
                                    'manga_id' => $manga->id,
                                    'chapter_number' => $chapterData['number'],
                                ],
                                [
                                    'title' => $chapterData['title'] ?? "Chapter {$chapterData['number']}",
                                    'source_url' => $chapterData['url'] ?? $chapterData['source_url'] ?? null,
                                    'source_published_at' => $chapterData['published_at'] ?? now(),
                                ]
                            );
                            $saved++;
                        } catch (\Exception $e) {
                             // Log individual failure
                        }
                    }
                });
                
                // Give DB a breather
                usleep(50000); // 50ms
                
            } catch (\Exception $e) {
                Log::error("Failed to save chapter batch", [
                    'manga' => $manga->title,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Update manga last_chapter_at
        if ($saved > 0) {
            $manga->update(['last_chapter_at' => now()]);
        }
        
        Log::info("Saved chapters", ['manga' => $manga->title, 'count' => $saved]);
    }
    
    /**
     * Fetch and save chapter images (on-demand or full)
     */
    public function scrapeChapterImages(Chapter $chapter, bool $downloadImages = false): array
    {
        try {
            Log::info("Fetching chapter images", [
                'manga' => $chapter->manga->title,
                'chapter' => $chapter->chapter_number
            ]);
            
            $imageUrls = $this->scraper->fetchChapterImages($chapter->source_url);
            
            if ($downloadImages && !empty($imageUrls)) {
                $localPaths = [];
                
                foreach ($imageUrls as $index => $url) {
                    $localPath = $this->imageService->downloadChapterImage(
                        $url,
                        $chapter->manga->slug,
                        $chapter->chapter_number,
                        $index + 1
                    );
                    
                    $localPaths[] = $localPath ?? $url;
                    
                    // Small delay between images
                    usleep(200000); // 200ms
                }
                
                // Save local paths to chapter
                $chapter->update(['images' => json_encode($localPaths)]);
                
                Log::info("Downloaded chapter images", [
                    'chapter' => $chapter->id,
                    'count' => count($localPaths)
                ]);
                
                return $localPaths;
            }
            
            return $imageUrls;
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch chapter images", [
                'chapter' => $chapter->id,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }
    
    /**
     * Get chapter images with proxy URLs for live loading + caching
     *  
     * This method scrapes images ON-DEMAND (first read only) 
     * and returns proxy URLs for automatic caching
     */
    public function getChapterImages(Chapter $chapter): array
    {
        // For live image loading strategy:
        // We scrape the chapter URL on-demand and return proxy URLs
        
        $manga = $chapter->manga;
        $chapterUrl = $chapter->url; // Original source URL
        
        if (!$chapterUrl) {
            Log::warning("Chapter {$chapter->id} has no source URL");
            return [];
        }
        
        try {
            // Scrape chapter images from source (using Puppeteer)
            $imageUrls = $this->scraper->fetchChapterImages($chapterUrl);
            
            if (empty($imageUrls)) {
                Log::warning("No images found for chapter {$chapter->id} at {$chapterUrl}");
                return [];
            }
            
            // Convert to proxy URLs for automatic caching
            $proxyUrls = array_map(function($imageUrl) {
                return route('image.proxy', [
                    'url' => $imageUrl,
                    'type' => 'chapter'
                ]);
            }, $imageUrls);
            
            Log::info("Fetched " . count($proxyUrls) . " images for chapter {$chapter->id}");
            
            return $proxyUrls;
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch chapter images for chapter {$chapter->id}", [
                'url' => $chapterUrl,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }
}
