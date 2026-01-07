@extends('layouts.app', ['isAdmin' => true])

@section('content')
<div class="admin-container">
    <div class="admin-header">
        <h1 class="admin-header__title">Komik-ID Scraper (Komikindo)</h1>
        <p class="admin-header__subtitle">Scrape data manga dari Komikindo.ch</p>
    </div>

    <!-- Scraper Control -->
    <div class="admin-section">
        <div class="admin-section__header">
            <div>
                <h2 class="admin-section__title">Kontrol Scraper</h2>
                <p class="admin-section__subtitle">Scraping berjalan unlimited. Hapus data lama memakai tombol khusus.</p>
            </div>
        </div>

        <form id="scraperForm" class="scraper-form">
            @csrf
            
            <button type="submit" id="submitBtn" class="btn btn--admin-primary btn--block">
                <span>Mulai Scraping Komikindo</span>
            </button>
        </form>

        <div class="scraper-danger-zone">
            <div class="scraper-danger-zone__text">
                <h3>Hapus Data Lama</h3>
                <p>Tombol ini akan menghapus semua manga, chapter, bookmark, komentar, dan riwayat baca.</p>
            </div>
            <button type="button" id="clearDataBtn" class="btn btn--danger">
                Hapus Data Lama
            </button>
        </div>

        <!-- Terminal Log Viewer -->
        <div class="terminal-window" id="terminalWindow" style="display:none; margin-top: 20px;">
            <div class="terminal-header">
                <span class="terminal-title">Scraper Terminal Log</span>
                <span class="terminal-status" id="terminalStatus">Waiting...</span>
                <button type="button" onclick="stopLogPolling()" style="background:none; border:none; color:white; font-size:12px; cursor:pointer;">Stop Polling</button>
            </div>
            <pre class="terminal-content" id="terminalContent">Ready to scrape...</pre>
        </div>

        <!-- Status Display -->
        <div id="scraperStatus" class="scraper-status" style="display: none;"></div>
    </div>
</div>

<style>
/* Reusing existing admin styles for consistency */
.terminal-window {
    background-color: #0d1117;
    border-radius: 6px;
    border: 1px solid #30363d;
    margin-top: 20px;
    font-family: 'Consolas', 'Monaco', monospace;
    display: flex;
    flex-direction: column;
    height: 400px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.terminal-header {
    background-color: #161b22;
    padding: 8px 15px;
    border-bottom: 1px solid #30363d;
    border-radius: 6px 6px 0 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.terminal-title {
    font-size: 0.8rem;
    color: #8b949e;
    font-weight: bold;
}

.terminal-status {
    font-size: 0.7rem;
    background: #238636;
    color: #fff;
    padding: 2px 8px;
    border-radius: 10px;
}

.terminal-content {
    flex: 1;
    padding: 15px;
    overflow-y: auto;
    color: #c9d1d9;
    font-size: 0.9rem;
    line-height: 1.5;
    white-space: pre-wrap;
    scrollbar-width: thin;
    scrollbar-color: #30363d #0d1117;
}
</style>

<script>
let logInterval;
const scraperStorageKey = 'komikid_scraper_running';
const content = document.getElementById('terminalContent');
const submitBtn = document.getElementById('submitBtn');
const status = document.getElementById('scraperStatus');

function setScraperUiRunning(isRunning) {
    if (isRunning) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Scraping berjalan...</span>';
        status.style.display = 'block';
        status.className = 'scraper-status scraper-status--loading';
        status.innerHTML = '<h3>Scraper sedang berjalan di background.</h3>';
    } else {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<span>Mulai Scraping Komikindo</span>';
    }
}

function startLogPolling() {
    // Show window
    document.getElementById('terminalWindow').style.display = 'flex';
    document.getElementById('terminalStatus').innerText = 'Running';
    content.innerText = 'Initializing...';
    localStorage.setItem(scraperStorageKey, '1');
    
    // Clear previous interval if any
    if(logInterval) clearInterval(logInterval);
    
    logInterval = setInterval(async () => {
        try {
            const res = await fetch('{{ route("admin.scraper.log") }}');
            const data = await res.json();
            
            if(data.content) {
                const isScrolledToBottom = content.scrollHeight - content.clientHeight <= content.scrollTop + 50;
                
                content.innerText = data.content;
                
                if(isScrolledToBottom) {
                    content.scrollTop = content.scrollHeight;
                }
                
                // Auto-stop if we see completion message
                if (data.content.includes('Scrape session completed') || data.content.includes('FATAL ERROR')) {
                     document.getElementById('terminalStatus').innerText = 'Finished';
                     localStorage.removeItem(scraperStorageKey);
                     setScraperUiRunning(false);
                     clearInterval(logInterval);
                }
            }
        } catch(e) {
            console.error("Log poll error", e);
        }
    }, 2000); // Poll every 2s
}

function stopLogPolling() {
    clearInterval(logInterval);
    document.getElementById('terminalStatus').innerText = 'Stopped';
    localStorage.removeItem(scraperStorageKey);
    setScraperUiRunning(false);
}

document.getElementById('scraperForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Show terminal immediately
    status.style.display = 'block';
    status.className = 'scraper-status scraper-status--loading';
    status.innerHTML = `<h3>Memulai background process...</h3>`;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Scraping berjalan...</span>';
    
    startLogPolling();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('{{ route("admin.scraper.run") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            status.innerHTML = `<h3>${data.message}</h3><p>Lihat log di atas untuk progress realtime.</p>`;
            status.className = 'scraper-status scraper-status--success';
        } else {
            status.innerHTML = `<h3>Gagal memulai: ${data.message}</h3>`;
             stopLogPolling();
        }
    } catch (error) {
         status.innerHTML = `<h3>Error Connect: ${error.message}</h3>`;
         stopLogPolling();
    }
});

document.getElementById('clearDataBtn').addEventListener('click', async function() {
    const confirmClear = confirm('Yakin ingin menghapus semua data manga lama? Tindakan ini tidak bisa dibatalkan.');
    if (!confirmClear) {
        return;
    }

    const status = document.getElementById('scraperStatus');
    status.style.display = 'block';
    status.className = 'scraper-status scraper-status--loading';
    status.innerHTML = `<h3>Menghapus data lama...</h3>`;

    try {
        const response = await fetch('{{ route("admin.scraper.clear") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        });

        const data = await response.json();
        if (data.success) {
            status.className = 'scraper-status scraper-status--success';
            status.innerHTML = `<h3>${data.message}</h3>`;
        } else {
            status.className = 'scraper-status scraper-status--error';
            status.innerHTML = `<h3>${data.message}</h3>`;
        }
    } catch (error) {
        status.className = 'scraper-status scraper-status--error';
        status.innerHTML = `<h3>Error: ${error.message}</h3>`;
    }
});

window.addEventListener('load', () => {
    if (localStorage.getItem(scraperStorageKey) === '1') {
        setScraperUiRunning(true);
        startLogPolling();
    }
});
</script>
@endsection

