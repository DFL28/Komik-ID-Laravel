# Komik-ID Laravel

Website pembaca manga/komik berbahasa Indonesia dengan sistem scraping otomatis, dibuat dengan Laravel 10.

## Features

- ✅ Browse manga dengan grid layout
- ✅ Detail manga dengan rating & genre
- ✅ Chapter reader dengan navigasi
- ✅ User authentication (register/login)
- ✅ Bookmark system
- ✅ Reading history tracking
- ✅ Comment system dengan nested replies
- ✅ Admin panel dengan scraper control
- ✅ Multi-source scraper (Komikindo, Komiku, WestManga, Maid)
- ✅ Dark theme UI (inspired by WestManga)
- ✅ SQLite database

## Tech Stack

- **Backend**: Laravel 10.x + PHP 8.2+
- **Database**: SQLite with Eloquent ORM
- **Frontend**: Blade templates + Vanilla JavaScript
- **Styling**: Pure CSS (no frameworks)
- **Scraper**: Guzzle + Simple HTML DOM Parser
- **Images**: Intervention Image

## Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- SQLite extension

### Setup Steps

1. **Install dependencies**:
```bash
composer install
```

2. **Setup environment**:
```bash
cp .env.example .env
```

3. **Edit .env file**:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite

DEFAULT_ADMIN_USERNAME=admin
DEFAULT_ADMIN_EMAIL=admin@komik-id.local
DEFAULT_ADMIN_PASSWORD=admin123
```

4. **Create database file**:
```bash
touch database/database.sqlite
```

5. **Generate app key**:
```bash
php artisan key:generate
```

6. **Run migrations**:
```bash
php artisan migrate
```

7. **Create admin user**:
```bash
php artisan tinker
>>> \App\Models\User::create(['username' => 'admin', 'email' => 'admin@local.test', 'password' => Hash::make('admin123'), 'is_admin' => true]);
```

8. **Link storage**:
```bash
php artisan storage:link
```

9. **Serve application**:
```bash
php artisan serve
```

10. **Access**:
- Homepage: http://localhost:8000
- Admin: http://localhost:8000/admin (username: admin, password: admin123)

## Usage

### Scraping Manga

1. Login sebagai admin
2. Go to Admin Panel → Scraper
3. Pilih source (Komikindo, dll)
4. Set jumlah pages
5. Click "Run Scraper"

### Reading Manga

1. Browse manga di homepage
2. Click manga untuk detail
3. Click chapter untuk membaca
4. Use navigation untuk next/prev chapter

### Bookmarks

1. Login terlebih dahulu
2. Di halaman detail manga, click "Bookmark"
3. Access bookmarks dari sidebar menu

## Project Structure

```
Komik-ID-Laravel/
├── app/
│   ├── Http/Controllers/      # Controllers
│   ├── Models/                 # Eloquent models
│   ├── Services/              # Business logic (Scraper, etc)
│   └── Helpers/               # Helper functions
├── config/                    # Configuration files
├── database/migrations/       # Database migrations
├── resources/views/           # Blade templates
├── public/                    # Public assets (CSS, JS)
├── routes/web.php            # Web routes
└── storage/                   # File storage
```

## Features Detail

### Authentication
- Register new user
- Login/Logout
- Session management
- Admin role protection

### Manga Browsing
- Grid layout dengan pagination
- Search by title
- Filter by genre
- Sort by popular/latest
- Rating display

### Chapter Reading
- Vertical scroll reader
- Next/prev chapter navigation
- Auto-track reading history
- Loading indicator

### Admin Panel
- Dashboard dengan statistics
- Scraper control
- User management
- Manga management

### Scraper System
- Multi-source support
- Rate limiting
- Error handling
- Progress tracking
- Automatic deduplication

## Configuration

Edit `.env` untuk mengubah:

```env
# Scraper settings
SCRAPER_DELAY_MS=500
SCRAPER_CONCURRENCY=5
SCRAPER_TIMEOUT=30

# Upload settings
UPLOAD_MAX_SIZE=5242880

# Cover image settings
COVER_IMAGE_WIDTH=400
COVER_IMAGE_QUALITY=80
```

## Deployment

### Production Setup

1. Set `APP_ENV=production` in `.env`
2. Run `composer install --optimize-autoloader --no-dev`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Setup web server (Apache/Nginx)
7. Point document root to `public/`

### Nginx Configuration Example

```nginx
server {
    listen 80;
    server_name komik-id.my.id;
    root /path/to/Komik-ID-Laravel/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Troubleshooting

### CSS/JS not loading
```bash
php artisan storage:link
chmod -R 755 public/
```

### Database errors
```bash
touch database/database.sqlite
php artisan migrate:fresh
```

### Permission errors
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

## License

MIT License

## Credits

- Design inspired by WestManga
- Converted from Node.js version to Laravel
