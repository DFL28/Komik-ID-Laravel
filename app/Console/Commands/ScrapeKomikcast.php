<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ScraperService;
use Illuminate\Support\Facades\Log;

class ScrapeKomikcast extends Command
{
    protected $signature = 'scraper:run {--pages=1} {--images=false}';
    protected $description = 'Run Komikcast Scraper in background';

    protected $scraperService;

    public function __construct(ScraperService $scraperService)
    {
        parent::__construct();
        $this->scraperService = $scraperService;
    }

    public function handle()
    {
        $pages = (int) $this->option('pages');
        $downloadImages = $this->option('images') !== 'false';

        Log::info("CLI Scraper started: Pages=$pages, Images=" . ($downloadImages ? 'Yes' : 'No'));

        try {
            // Scraper service already logs to storage/logs/scraper.log
            $result = $this->scraperService->scrape($pages, $downloadImages);
            
            $this->info("Scraping completed. Count: " . $result['count']);
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error("Scraping failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
