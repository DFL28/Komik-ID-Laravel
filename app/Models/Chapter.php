<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'manga_id',
        'chapter_number',
        'title',
        'source_url',
        'source_published_at',
        'pages_data',
    ];

    protected function casts(): array
    {
        return [
            'source_published_at' => 'datetime',
            'pages_data' => 'array',
        ];
    }

    // Relationships
    public function manga()
    {
        return $this->belongsTo(Manga::class);
    }

    public function readingHistory()
    {
        return $this->hasMany(ReadingHistory::class);
    }

    // Helper methods
    public function getPagesArray(): array
    {
        return $this->pages_data ?? [];
    }

    public function setPagesArray(array $pages): void
    {
        $this->pages_data = $pages;
    }
    
    /**
     * Accessor for 'url' attribute (alias for source_url)
     */
    public function getUrlAttribute()
    {
        return $this->source_url;
    }

    public function getNextChapter()
    {
        // Get sorted list of all chapters for this manga
        // This is safer than single query logic because of potential string/float precision issues
        $allChapters = self::where('manga_id', $this->manga_id)
            ->get()
            ->sortBy(function($chapter) {
                return (float) $chapter->chapter_number;
            });
            
        // Find current position and get next
        $foundCurrent = false;
        foreach ($allChapters as $chapter) {
            if ($foundCurrent) {
                return $chapter;
            }
            if ((float)$chapter->chapter_number == (float)$this->chapter_number) {
                $foundCurrent = true;
            }
        }
        
        return null;
    }

    public function getPrevChapter()
    {
        // Same logic but reversed
        $allChapters = self::where('manga_id', $this->manga_id)
            ->get()
            ->sortByDesc(function($chapter) {
                return (float) $chapter->chapter_number;
            });
            
        $foundCurrent = false;
        foreach ($allChapters as $chapter) {
            if ($foundCurrent) {
                return $chapter;
            }
            if ((float)$chapter->chapter_number == (float)$this->chapter_number) {
                $foundCurrent = true;
            }
        }
        
        return null;
    }
}
