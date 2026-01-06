<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AICategorizationService
{
    protected $apiKey;
    protected $apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
    
    // Daftar genre manga yang valid
    protected $validGenres = [
        'Action', 'Adventure', 'Comedy', 'Drama', 'Fantasy', 'Horror',
        'Mystery', 'Romance', 'Sci-Fi', 'Slice of Life', 'Sports',
        'Supernatural', 'Thriller', 'Psychological', 'Historical',
        'Isekai', 'Shounen', 'Shoujo', 'Seinen', 'Josei', 'Harem',
        'Mecha', 'School Life', 'Martial Arts', 'Magic', 'Ecchi'
    ];
    
    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
    }
    
    /**
     * Kategorisasi manga berdasarkan title dan description
     */
    public function categorizeManga(string $title, ?string $description = null): array
    {
        if (empty($this->apiKey)) {
            Log::warning('Gemini API key not configured');
            return $this->fallbackCategorization($title, $description);
        }
        
        try {
            $prompt = $this->buildPrompt($title, $description);
            $response = $this->callGeminiAPI($prompt);
            
            return $this->parseResponse($response);
            
        } catch (\Exception $e) {
            Log::error('AI Categorization failed: ' . $e->getMessage());
            return $this->fallbackCategorization($title, $description);
        }
    }
    
    /**
     * Build prompt untuk Gemini
     */
    protected function buildPrompt(string $title, ?string $description): string
    {
        $genreList = implode(', ', $this->validGenres);
        
        $prompt = "Analyze this manga and extract 3-5 most relevant genres from this list: {$genreList}\n\n";
        $prompt .= "Title: {$title}\n";
        
        if ($description) {
            $cleanDesc = substr(strip_tags($description), 0, 500);
            $prompt .= "Description: {$cleanDesc}\n";
        }
        
        $prompt .= "\nRespond ONLY with a comma-separated list of genres, nothing else. Example: Action, Fantasy, Adventure";
        
        return $prompt;
    }
    
    /**
     * Call Gemini API
     */
    protected function callGeminiAPI(string $prompt): string
    {
        $response = Http::timeout(10)
            ->post($this->apiEndpoint . '?key=' . $this->apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.3,
                    'maxOutputTokens' => 100,
                ]
            ]);
        
        if (!$response->successful()) {
            throw new \Exception('Gemini API request failed: ' . $response->status());
        }
        
        $data = $response->json();
        
        if (empty($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new \Exception('Invalid response from Gemini API');
        }
        
        return $data['candidates'][0]['content']['parts'][0]['text'];
    }
    
    /**
     * Parse response dari Gemini
     */
    protected function parseResponse(string $response): array
    {
        // Clean response
        $response = trim($response);
        $response = str_replace(["\n", "\r", '**', '*'], '', $response);
        
        // Split by comma
        $genres = array_map('trim', explode(',', $response));
        
        // Validate genres
        $validatedGenres = [];
        foreach ($genres as $genre) {
            // Find closest match in valid genres (case-insensitive)
            foreach ($this->validGenres as $validGenre) {
                if (stripos($genre, $validGenre) !== false || stripos($validGenre, $genre) !== false) {
                    $validatedGenres[] = $validGenre;
                    break;
                }
            }
        }
        
        return array_unique(array_slice($validatedGenres, 0, 5));
    }
    
    /**
     * Improved fallback categorization tanpa AI (keyword-based + pattern matching)
     */
    protected function fallbackCategorization(string $title, ?string $description): array
    {
        $text = strtolower($title . ' ' . ($description ?? ''));
        $genres = [];
        
        // Enhanced keyword mapping dengan lebih banyak variations
        $keywords = [
            'Action' => [
                'fight', 'battle', 'war', 'action', 'combat', 'warrior', 'sword', 
                'assassin', 'hunter', 'martial', 'kill', 'fighter', 'weapon',
                'strongest', 'power', 'level up', 'dungeon', 'monster', 'boss'
            ],
            'Romance' => [
                'love', 'romance', 'couple', 'relationship', 'dating', 'girlfriend',
                'boyfriend', 'marriage', 'wedding', 'crush', 'heart', 'kiss',
                'confession', 'romantic'
            ],
            'Comedy' => [
                'comedy', 'funny', 'hilarious', 'humor', 'laugh', 'joke',
                'gag', 'parody', 'silly', 'comic'
            ],
            'Fantasy' => [
                'magic', 'fantasy', 'wizard', 'dragon', 'spell', 'mage',
                'demon', 'angel', 'divine', 'mythical', 'legendary', 'hero',
                'goddess', 'god', 'realm', 'kingdom', 'sword', 'sorcerer'
            ],
            'Isekai' => [
                'isekai', 'reincarnated', 'another world', 'transported',
                'reborn', 'transmigrat', 'rebirth', 'previous life', 'past life',
                'different world', 'parallel world', 'otherworld'
            ],
            'School Life' => [
                'school', 'student', 'classroom', 'academy', 'university',
                'college', 'campus', 'class', 'teacher', 'high school',
                'middle school'
            ],
            'Slice of Life' => [
                'daily', 'everyday', 'slice of life', 'daily life', 'normal life',
                'ordinary', 'mundane', 'routine'
            ],
            'Drama' => [
                'drama', 'tragic', 'emotional', 'tear', 'sad', 'conflict',
                'struggle', 'suffer', 'pain', 'tragedy'
            ],
            'Mystery' => [
                'mystery', 'detective', 'investigation', 'crime', 'murder',
                'case', 'solve', 'clue', 'secret'
            ],
            'Horror' => [
                'horror', 'scary', 'ghost', 'zombie', 'terror', 'fear',
                'creepy', 'haunted', 'nightmare'
            ],
            'Supernatural' => [
                'supernatural', 'spirit', 'ghost', 'paranormal', 'curse',
                'possessed', 'exorcist', 'yokai', 'demon'
            ],
            'Shounen' => [
                'shounen', 'shonen', 'boy', 'youth', 'young boy'
            ],
            'Seinen' => [
                'seinen', 'adult', 'mature', 'dark'
            ],
            'Harem' => [
                'harem', 'multiple girls', 'many girls', 'polygamy'
            ],
            'Psychological' => [
                'psychological', 'mind', 'mental', 'psycho', 'insanity',
                'manipulation', 'twisted'
            ],
            'Martial Arts' => [
                'martial arts', 'kung fu', 'karate', 'murim', 'wuxia',
                'cultivation', 'qi', 'chi'
            ],
            'Adventure' => [
                'adventure', 'journey', 'quest', 'explore', 'expedition',
                'travel', 'voyage'
            ],
            'Sci-Fi' => [
                'sci-fi', 'science fiction', 'space', 'robot', 'ai',
                'virtual reality', 'vr', 'cyber', 'future', 'technology'
            ],
            'Sports' => [
                'sport', 'soccer', 'basketball', 'baseball', 'volleyball',
                'tennis', 'game', 'match', 'tournament', 'athlete'
            ],
        ];
        
        // Pattern-based detection (lebih sophisticated)
        $patterns = [
            'Isekai' => [
                '/reincarnated as/i',
                '/transported to/i', 
                '/summoned to/i',
                '/died and/i',
                '/reborn as/i'
            ],
            'Revenge' => [
                '/revenge/i',
                '/betrayed/i',
                '/regret/i'
            ],
            'System' => [
                '/system/i',
                '/status window/i',
                '/level \d+/i'
            ]
        ];
        
        // Keyword matching
        foreach ($keywords as $genre => $words) {
            $matchCount = 0;
            foreach ($words as $word) {
                if (str_contains($text, $word)) {
                    $matchCount++;
                    if ($matchCount >= 1) { // At least 1 match
                        if (!in_array($genre, $genres)) {
                            $genres[] = $genre;
                        }
                        break;
                    }
                }
            }
        }
        
        // Pattern matching (regex)
        foreach ($patterns as $genre => $regexes) {
            foreach ($regexes as $regex) {
                if (preg_match($regex, $text)) {
                    if (!in_array($genre, $genres)) {
                        $genres[] = $genre;
                    }
                    break;
                }
            }
        }
        
        // Title-based quick detection
        if (preg_match('/\b(manhwa|manhua|manga)\b/i', $title)) {
            // Already categorized, good
        }
        
        // Return genres or default
        return !empty($genres) ? array_slice(array_unique($genres), 0, 5) : ['Manga'];
    }
    
    /**
     * Batch categorization untuk multiple manga
     */
    public function batchCategorize(array $mangaList): array
    {
        $results = [];
        
        foreach ($mangaList as $manga) {
            $genres = $this->categorizeManga(
                $manga['title'],
                $manga['description'] ?? null
            );
            
            $results[$manga['slug']] = $genres;
            
            // Rate limiting
            usleep(500000); // 500ms delay
        }
        
        return $results;
    }
}
