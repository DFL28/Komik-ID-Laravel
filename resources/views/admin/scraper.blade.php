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
                <p class="admin-section__subtitle">Scraping akan berjalan otomatis untuk semua halaman.</p>
            </div>
        </div>

        <form id="scraperForm" class="scraper-form">
            @csrf
            
            <div class="form-group">
                 <label class="form-checkbox">
                    <input type="checkbox" id="download_images" name="download_images" value="1">
                    <span>Download Gambar (Cover & Chapter)</span>
                 </label>
                 <span class="form-hint">Download gambar lokal (lambat & boros space). Uncheck untuk mode cepat (Remote Image).</span>
            </div>

            <div class="form-group">
                 <label class="form-checkbox">
                    <input type="checkbox" id="reset_data" name="reset_data" value="1">
                    <span>Hapus data komik lama sebelum scrape</span>
                 </label>
                 <span class="form-hint">Ini akan menghapus manga, chapter, bookmark, komentar, dan riwayat baca.</span>
            </div>
            
            <button type="submit" id="submitBtn" class="btn btn--admin-primary btn--block">
                <span>Mulai Scraping Komikindo (Unlimited)</span>
            </button>
        </form>

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
const content = document.getElementById('terminalContent');

function startLogPolling() {
    // Show window
    document.getElementById('terminalWindow').style.display = 'flex';
    document.getElementById('terminalStatus').innerText = 'Running';
    content.innerText = 'Initializing...';
    
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
                     document.getElementById('submitBtn').disabled = false;
                     document.getElementById('submitBtn').innerHTML = '<span>Mulai Scraping Komikindo (Unlimited)</span>';
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
}

document.getElementById('scraperForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const status = document.getElementById('scraperStatus');
    const submitBtn = document.getElementById('submitBtn');
    
    // Show terminal immediately
    status.style.display = 'block';
    status.className = 'scraper-status scraper-status--loading';
    status.innerHTML = `<h3>Memulai background process...</h3>`;
    submitBtn.disabled = true;
    
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
             submitBtn.disabled = false;
        }
    } catch (error) {
         status.innerHTML = `<h3>Error Connect: ${error.message}</h3>`;
         stopLogPolling();
         submitBtn.disabled = false;
    }
});
</script>
@endsection

