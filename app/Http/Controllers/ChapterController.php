<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Models\Chapter;
use App\Models\ReadingHistory;
use Illuminate\Http\Request;

class ChapterController extends Controller
{
    public function read($slug, $number)
    {
        $manga = Manga::where('slug', $slug)->firstOrFail();
        
        $chapter = Chapter::where('manga_id', $manga->id)
            ->where('chapter_number', $number)
            ->firstOrFail();
        
        // Get images from scraper service (cached or live)
        $scraperService = app(\App\Services\ScraperService::class);
        $pages = $scraperService->getChapterImages($chapter);
        
        $nextChapter = $chapter->getNextChapter();
        $prevChapter = $chapter->getPrevChapter();
        
        // Get all chapters for navigation
        $allChapters = $manga->chapters()
            ->orderByRaw('CAST(chapter_number AS DECIMAL) ASC')
            ->get();
        
        // Track reading history
        if (auth()->check()) {
            ReadingHistory::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'manga_id' => $manga->id,
                    'chapter_number' => $number,
                ],
                [
                    'chapter_id' => $chapter->id,
                ]
            );
        }
        
        return view('reader', compact(
            'manga',
            'chapter',
            'pages',
            'nextChapter',
            'prevChapter',
            'allChapters'
        ));
    }
}
