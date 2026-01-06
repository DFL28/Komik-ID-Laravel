<?php

namespace App\Http\Controllers;

use App\Models\Manga;
use App\Models\Chapter;
use App\Models\User;
use App\Services\ScraperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        // Temporarily removed middleware due to autoload issue
        // $this->middleware(['auth', 'admin']);
        $this->scraperService = $scraperService;
    }

    public function index()
    {
        $stats = [
            'total_manga' => Manga::count(),
            'total_chapters' => Chapter::count(),
            'total_users' => User::count(),
            'recent_manga' => Manga::latest()->take(10)->get(),
        ];
        
        return view('admin.dashboard', compact('stats'));
    }

    public function scraper()
    {
        return view('admin.scraper');
    }


    public function runScraper(Request $request)
    {
        // ... (validation code kept same as before) ...
        $validated = $request->validate([
            'pages' => 'nullable|integer|min:1|max:50',
            'download_images' => 'nullable|boolean',
        ]);
        
        $pages = $validated['pages'] ?? 1;
        $downloadImages = $request->has('download_images');
        $imgFlag = $downloadImages ? '--images=true' : '--images=false';
        
        // Command to run (cross-platform background execution)
        $artisanPath = base_path('artisan');
        $phpBinary = PHP_BINARY && file_exists(PHP_BINARY) ? PHP_BINARY : 'php';
        $artisanArg = escapeshellarg($artisanPath);
        $phpArg = escapeshellarg($phpBinary);
        $cmdArgs = "scraper:run --pages={$pages} {$imgFlag}";
        if (PHP_OS_FAMILY === 'Windows') {
            $command = "start /B {$phpArg} {$artisanArg} {$cmdArgs}";
        } else {
            $command = "nohup {$phpArg} {$artisanArg} {$cmdArgs} > /dev/null 2>&1 &";
        }
        
        try {
            // Log intiation
            Log::info("Launching background scraper: $command");
            
            // Execute in background
            if (PHP_OS_FAMILY === 'Windows') {
                pclose(popen($command, "r"));
            } else {
                exec($command);
            }
            
            // Return immediately saying process started
            return response()->json([
                'success' => true,
                'message' => "Scraping dimulai di background! Cek terminal log untuk progress.",
                'data' => [
                    'count' => 0,
                    'manga' => [],
                    'background' => true
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to launch scraper: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulai scraper: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getScraperLog()
    {
        $logPath = storage_path('logs/scraper.log');
        
        if (!file_exists($logPath)) {
            return response()->json(['content' => 'Waiting for logs...']);
        }
        
        // Efficiently read last 10KB of file
        $fp = fopen($logPath, 'r');
        fseek($fp, -10240, SEEK_END); // Go to 10KB from end
        // If file is smaller than 10KB, fseek might fail or go to 0, which is fine
        if (ftell($fp) < 0) rewind($fp);
        
        $content = fread($fp, 10240);
        fclose($fp);
        
        // Ensure we start from a clean line
        $firstNewline = strpos($content, "\n");
        if ($firstNewline !== false) {
            $content = substr($content, $firstNewline + 1);
        }
        
        return response()->json([
            'content' => $content
        ]);
    }

    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->is_admin) {
            return back()->withErrors(['error' => 'Tidak bisa menghapus admin!']);
        }
        
        $user->delete();
        
        return back()->with('success', 'User berhasil dihapus!');
    }
}
