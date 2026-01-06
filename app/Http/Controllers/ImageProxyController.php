<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageProxyController extends Controller
{
    /**
     * Proxy and cache manga images
     * 
     * Usage: /image-proxy?url=https://...&type=cover|chapter
     */
    public function proxy(Request $request)
    {
        $url = $request->get('url');
        $type = $request->get('type', 'chapter'); // cover | chapter
        
        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            abort(400, 'Invalid URL');
        }
        
        // Create cache key from URL
        $cacheKey = 'img_' . md5($url);
        $cacheDir = 'cache/images/' . $type;
        $cachePath = $cacheDir . '/' . $cacheKey . '.jpg';
        
        // Check if already cached in storage
        if (Storage::disk('public')->exists($cachePath)) {
            return response()->file(storage_path('app/public/' . $cachePath));
        }
        
        // Check memory cache (2 hour)
        $imageData = Cache::remember($cacheKey, 7200, function () use ($url) {
            try {
                // Fetch image from external source
                $response = Http::timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                        'Referer' => parse_url($url, PHP_URL_SCHEME) . '://' . parse_url($url, PHP_URL_HOST),
                        'Accept' => 'image/avif,image/webp,image/apng,image/*,*/*;q=0.8',
                    ])
                    ->get($url);
                
                if ($response->successful()) {
                    return $response->body();
                }
                
                return null;
            } catch (\Exception $e) {
                \Log::error("Image proxy error for {$url}: " . $e->getMessage());
                return null;
            }
        });
        
        if (!$imageData) {
            // Return placeholder image
            abort(404, 'Image not found');
        }
        
        // Save to disk cache for long-term storage
        Storage::disk('public')->makeDirectory($cacheDir);
        Storage::disk('public')->put($cachePath, $imageData);
        
        // Detect content type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);
        
        // Return image with correct headers
        return response($imageData)
            ->header('Content-Type', $mimeType)
            ->header('Cache-Control', 'public, max-age=2592000') // 30 days
            ->header('Access-Control-Allow-Origin', '*');
    }
    
    /**
     * Clear image cache
     */
    public function clearCache()
    {
        Storage::disk('public')->deleteDirectory('cache/images');
        Cache::flush();
        
        return response()->json([
            'success' => true,
            'message' => 'Image cache cleared successfully'
        ]);
    }
}
