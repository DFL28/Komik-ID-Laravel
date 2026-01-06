<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Manga extends Model
{
    use HasFactory;

    protected $table = 'manga';

    protected $fillable = [
        'slug',
        'title',
        'alternative_title',
        'description',
        'cover_path',
        'author',
        'status',
        'type',
        'genres',
        'rating',
        'country',
        'content_type',
        'last_chapter_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'last_chapter_at' => 'datetime',
        ];
    }

    // Relationships
    public function chapters()
    {
        return $this->hasMany(Chapter::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function readingHistory()
    {
        return $this->hasMany(ReadingHistory::class);
    }

    // Accessors & Mutators
    public function getGenresArrayAttribute()
    {
        return $this->genres ? explode(',', $this->genres) : [];
    }

    public function setGenresAttribute($value)
    {
        $this->attributes['genres'] = is_array($value) ? implode(',', $value) : $value;
    }

    // Helper methods
    public static function createSlug(string $title): string
    {
        return Str::slug($title);
    }

    public function getLatestChapter()
    {
        return $this->chapters()
            ->orderByRaw('CAST(chapter_number AS DECIMAL) DESC')
            ->first();
    }

    public function getFirstChapter()
    {
        return $this->chapters()
            ->orderByRaw('CAST(chapter_number AS DECIMAL) ASC')
            ->first();
    }

    // Scopes
    public function scopePopular($query)
    {
        return $query->withCount('bookmarks')->orderBy('bookmarks_count', 'desc');
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('last_chapter_at', 'desc');
    }

    public function scopeByGenre($query, string $genre)
    {
        return $query->where('genres', 'like', "%{$genre}%");
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('alternative_title', 'like', "%{$search}%");
        });
    }
}
