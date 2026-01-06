<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ImageService
{
    /**
     * Download and save image from URL
     */
    public function downloadAndSave(string $url, string $path): ?string
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'Referer' => 'https://komikcast.co.id/'
                ])
                ->get($url);
            
            if (!$response->successful()) {
                Log::warning("Failed to download image", ['url' => $url]);
                return null;
            }
            
            // Save to storage
            Storage::disk('public')->put($path, $response->body());
            
            return $path;
            
        } catch (\Exception $e) {
            Log::error("Image download failed", [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Download cover image for manga
     */
    public function downloadCover(string $url, string $slug): ?string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = Str::slug($slug) . '.' . $extension;
        $path = 'manga/covers/' . $filename;
        
        return $this->downloadAndSave($url, $path);
    }
    
    /**
     * Download chapter image
     */
    public function downloadChapterImage(string $url, string $mangaSlug, float $chapterNumber, int $pageNumber): ?string
    {
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = sprintf('page-%03d.%s', $pageNumber, $extension);
        $path = sprintf('manga/%s/chapter-%s/%s', 
            Str::slug($mangaSlug), 
            str_replace('.', '-', $chapterNumber), 
            $filename
        );
        
        return $this->downloadAndSave($url, $path);
    }
    
    /**
     * Check if image exists in storage
     */
    public function exists(string $path): bool
    {
        return Storage::disk('public')->exists($path);
    }
    
    /**
     * Get image URL from storage path
     */
    public function url(string $path): string
    {
        return Storage::disk('public')->url($path);
    }
}
