<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ImageProxyController;
use Illuminate\Support\Facades\Route;

// Image Proxy (public - for caching external images)
Route::get('/img-proxy', [ImageProxyController::class, 'proxy'])->name('image.proxy');

// Public routes
Route::get('/', [MangaController::class, 'index'])->name('home');
Route::get('/manga/{slug}', [MangaController::class, 'detail'])->name('manga.detail');
Route::get('/manga/{slug}/chapter/{number}', [ChapterController::class, 'read'])->name('chapter.read');
Route::get('/search', [MangaController::class, 'search'])->name('search');
Route::get('/genre', [MangaController::class, 'genre'])->name('genre');
Route::get('/type/{type}', [MangaController::class, 'byType'])->name('type');
Route::get('/populer', function() {
    return redirect('/?sort=popular');
})->name('popular');
Route::get('/terbaru', function() {
    return redirect('/?sort=latest');
})->name('latest');

// Auth routes
// Temporarily removed guest middleware due to autoload issue
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/signup', [RegisterController::class, 'showRegister'])->name('register');
Route::post('/signup', [RegisterController::class, 'register']);

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// User authenticated routes - Temporarily removed auth middleware
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::post('/profile', [ProfileController::class, 'update']);
Route::post('/profile/password', [ProfileController::class, 'updatePassword']);
Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);

Route::get('/bookmark', [MangaController::class, 'bookmarks'])->name('bookmarks');
Route::post('/manga/{slug}/bookmark', [MangaController::class, 'toggleBookmark'])->name('manga.bookmark');

// Comment API routes - Temporarily removed auth middleware
Route::prefix('api')->group(function () {
    Route::post('/comments', [CommentController::class, 'store']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});

// Admin routes
// Admin routes - Temporarily removed auth/admin middleware
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/scraper', [AdminController::class, 'scraper'])->name('admin.scraper');
    Route::get('/scraper/log', [AdminController::class, 'getScraperLog'])->name('admin.scraper.log');
    Route::post('/scraper/run', [AdminController::class, 'runScraper'])->name('admin.scraper.run');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
});
