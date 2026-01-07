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
        $status = $request->get('status');
        $contentType = $request->get('content_type');
        $type = $request->get('type');
        $order = $request->get('order', 'default');
        $color = $request->get('color');

        $genres = $this->getAllGenres();

        $query = Manga::query();

        if ($genre) {
            $query->byGenre($genre);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($contentType) {
            $query->where('type', $contentType);
        }

        if ($type) {
            $query->where('content_type', $type);
        }

        if ($color === 'color') {
            $query->where('content_type', 'color');
        } elseif ($color === 'bw') {
            $query->where(function ($q) {
                $q->whereNull('content_type')
                  ->orWhere('content_type', '!=', 'color');
            });
        }

        switch ($order) {
            case 'latest':
                $query->orderBy('last_chapter_at', 'desc');
                break;
            case 'popular':
                $query->withCount('bookmarks')
                      ->orderBy('bookmarks_count', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'title':
                $query->orderBy('title');
                break;
            default:
                $query->orderBy('updated_at', 'desc');
        }

        $manga = $query->paginate(20)->appends($request->query());

        $contentTypes = Manga::query()
            ->whereNotNull('type')
            ->select('type')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();

        $typeOptions = Manga::query()
            ->whereNotNull('content_type')
            ->select('content_type')
            ->distinct()
            ->orderBy('content_type')
            ->pluck('content_type')
            ->toArray();

        return view('genre', compact(
            'manga',
            'genres',
            'genre',
            'status',
            'contentType',
            'type',
            'order',
            'color',
            'contentTypes',
            'typeOptions'
        ));
    }

    public function toggleBookmark(Request $request, $slug)
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            return redirect()->route('login');
        }
        
        $manga = Manga::where('slug', $slug)->firstOrFail();
        
        $bookmarkQuery = Bookmark::where('user_id', auth()->id())
            ->where('manga_id', $manga->id);

        $bookmarked = false;
        if ($bookmarkQuery->exists()) {
            $bookmarkQuery->delete();
            $bookmarked = false;
        } else {
            Bookmark::firstOrCreate([
                'user_id' => auth()->id(),
                'manga_id' => $manga->id,
            ]);
            $bookmarked = true;
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'bookmarked' => $bookmarked,
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
