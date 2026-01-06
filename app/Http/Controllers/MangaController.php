<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Models\Bookmark;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;

class MangaController extends Controller
{
    public function index(Request $request)
    {
        // Latest Updates (10 most recent)
        $latestUpdates = Manga::whereNotNull('last_chapter_at')
            ->orderBy('last_chapter_at', 'desc')
            ->limit(10)
            ->get();
        
        // Manhwa (Korean)
        $manhwa = Manga::where('type', 'Manhwa')
            ->orderBy('last_chapter_at', 'desc')
            ->limit(10)
            ->get();
        
        // Manhua (Chinese)
        $manhua = Manga::where('type', 'Manhua')
            ->orderBy('last_chapter_at', 'desc')
            ->limit(10)
            ->get();
        
        // Manga (Japanese)
        $manga = Manga::where('type', 'Manga')
            ->orderBy('last_chapter_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('home', compact('latestUpdates', 'manhwa', 'manhua', 'manga'));
    }

    public function byType($type)
    {
        $validTypes = ['manhwa', 'manhua', 'manga'];
        $type = strtolower($type);
        
        if (!in_array($type, $validTypes)) {
            abort(404);
        }
        
        $manga = Manga::where('type', ucfirst($type))
            ->orderBy('last_chapter_at', 'desc')
            ->paginate(20);
        
        return view('type', compact('manga', 'type'));
    }

    public function detail($slug)
    {
        $manga = Manga::where('slug', $slug)->firstOrFail();
        
        $chapters = $manga->chapters()
            ->orderByRaw('CAST(chapter_number AS DECIMAL) DESC')
            ->get();
        
        $isBookmarked = false;
        $readChapters = [];
        
        if (auth()->check()) {
            $isBookmarked = Bookmark::where('user_id', auth()->id())
                ->where('manga_id', $manga->id)
                ->exists();
            
            $readChapters = ReadingHistory::where('user_id', auth()->id())
                ->where('manga_id', $manga->id)
                ->pluck('chapter_number')
                ->toArray();
        }
        
        $comments = $manga->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->limit(10)
            ->get();
        
        $commentsCount = $manga->comments()->whereNull('parent_id')->count();
        
        // Get related manga based on matching genres
        $relatedManga = Manga::where('id', '!=', $manga->id)
            ->when($manga->genres, function($query) use ($manga) {
                $genres = explode(',', $manga->genres);
                foreach ($genres as $genre) {
                    $query->orWhere('genres', 'LIKE', '%' . trim($genre) . '%');
                }
            })
            ->limit(7)
            ->get();
        
        return view('detail', compact(
            'manga',
            'chapters',
            'isBookmarked',
            'readChapters',
            'comments',
            'commentsCount',
            'relatedManga'
        ));
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        
        $manga = Manga::search($query)->paginate(20);
        
        return view('search', compact('manga', 'query'));
    }

    public function genre(Request $request)
    {
        $genre = $request->get('genre');
        $genres = $this->getAllGenres();
        
        $query = Manga::query();
        
        if ($genre) {
            $query->byGenre($genre);
        }
        
        $manga = $query->latest()->paginate(20);
        
        return view('genre', compact('manga', 'genres', 'genre'));
    }

    public function toggleBookmark($slug)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $manga = Manga::where('slug', $slug)->firstOrFail();
        
        $bookmark = Bookmark::where('user_id', auth()->id())
            ->where('manga_id', $manga->id)
            ->first();
        
        if ($bookmark) {
            $bookmark->delete();
        } else {
            Bookmark::create([
                'user_id' => auth()->id(),
                'manga_id' => $manga->id,
            ]);
        }
        
        return back();
    }

    public function bookmarks()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }
        
        $manga = Manga::whereHas('bookmarks', function($q) {
            $q->where('user_id', auth()->id());
        })->latest()->paginate(20);
        
        return view('bookmarks', compact('manga'));
    }

    private function getAllGenres()
    {
        $allGenres = Manga::whereNotNull('genres')
            ->pluck('genres')
            ->flatMap(fn($g) => explode(',', $g))
            ->map(fn($g) => trim($g))
            ->unique()
            ->sort()
            ->values();
        
        return $allGenres;
    }
}
