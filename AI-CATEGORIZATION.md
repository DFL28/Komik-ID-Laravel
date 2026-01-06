# 🤖 AI Auto-Categorization System

Sistem kategorisasi otomatis menggunakan Gemini AI untuk menganalisa dan memberikan genre/kategori yang tepat untuk manga berdasarkan judul dan deskripsi.

## ✨ Fitur

1. **Auto-Categorization**: Otomatis kategorikan manga saat scraping
2. **Gemini AI**: Menggunakan Google Gemini 1.5 Flash (gratis)
3. **Fallback System**: Jika AI gagal, gunakan keyword-based categorization
4. **Smart Trigger**: Hanya aktif jika genre kosong atau < 2 genre

## 🔧 Setup

### 1. Dapatkan Gemini API Key (GRATIS)

1. Buka: https://ai.google.dev/
2. Klik "Get API Key" → "Create API key"
3. Copy API key yang didapat

### 2. Tambahkan ke .env

Buka file `.env` dan tambahkan:

```env
GEMINI_API_KEY=your_api_key_here
```

### 3. Test AI Categorization

```bash
php test-ai-categorization.php
```

Output contoh:
```
🤖 Testing AI Categorization Service
=====================================

✓ Gemini API Key configured

[1] Testing: Solo Leveling
------------------------------------------------------------
Genres: Action, Fantasy, Adventure, Shounen

[2] Testing: My Girlfriend is a Stalker
------------------------------------------------------------
Genres: Romance, Comedy, School Life
```

## 📖 Cara Kerja

Saat scraping:

1. **Cek Genre**: System cek apakah manga sudah punya genre dari source
2. **Trigger AI**: Jika genre kosong atau < 2, panggil Gemini AI
3. **Analyze**: Gemini analyze title + description
4. **Extract**: AI extract 3-5 genre paling relevan
5. **Validate**: Validasi dengan daftar genre yang valid
6. **Save**: Simpan genre ke database

## 🎯 Genre yang Didukung

```
Action, Adventure, Comedy, Drama, Fantasy, Horror,
Mystery, Romance, Sci-Fi, Slice of Life, Sports,
Supernatural, Thriller, Psychological, Historical,
Isekai, Shounen, Shoujo, Seinen, Josei, Harem,
Mecha, School Life, Martial Arts, Magic, Ecchi
```

## 📊 Fallback Mode

Jika API key tidak di-set atau AI gagal:
- Sistem otomatis gunakan **keyword-based categorization**
- Analisa keywords di title & description
- Assign genre berdasarkan keyword match
- Minimal reliabilitas tetap terjaga

## 🔍 Log Example

```
[2025-12-22 18:00:00] INFO: Processing manga...
[2025-12-22 18:00:01] INFO: 🤖 AI Categorization for: Solo Leveling
[2025-12-22 18:00:02] INFO: ✓ AI found genres: Action, Fantasy, Adventure, Shounen
[2025-12-22 18:00:03] INFO: Success: Solo Leveling
```

## 💡 Tips

- **Free Tier**: Gemini API free tier = 60 requests/minute
- **Rate Limiting**: System sudah include 500ms delay between requests
- **Fallback Ready**: Jika quota habis, auto-switch ke keyword mode
- **No Breaking**: Scraping tetap jalan meski AI error

## 🚀 Ready to Use!

Sistem sudah terintegrasi dengan scraper. Cukup:

1. Set API key di `.env`
2. Scrape manga seperti biasa
3. AI otomatis categorize!

Tanpa API key juga tetap jalan dengan fallback mode.
